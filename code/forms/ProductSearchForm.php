<?php

/**
 * @package members_directory
 */

/**
 * More advanced search form
 * @package business_directory
 */
class ProductSearchForm extends SearchForm {

  /**
   * the constructor of a Simple/basic SearchForm
   */
  function __construct($controller, $name, $fields = null, $actions = null) {
    if(!$fields) {
      $fields = new FieldSet(
        $searchBy = new CompositeField(
          new HeaderField(_t('ProductSearchForm.SEARCHBY', 'Search Products'),2),

          new HeaderField(_t('ProductSearchForm.WHERE', 'Where?'),3),
          new LocationTreeDropdown('Location',''),
          $categoryFields = new CompositeField (
            new HeaderField(_t('ProductSearchForm.SEARCHCRITERIA', 'Search Criteria'),3),
            new MyTypeDropdown("ProductCategory1", 'Category #1', 'ProductCategory',null,null,false,array('Any'=>'Any Category')),
            new LiteralField('or1',' or '),
            new MyTypeDropdown("ProductCategory2", 'Category #2', 'ProductCategory',null,null,true,array(''=>'')),
            new LiteralField('or2',' or '),
            new MyTypeDropdown("ProductCategory3", 'Category #3', 'ProductCategory',null,null,true,array(''=>''))
          ),
          new TextField("keywords", _t('ProductSearchForm.KEYWORDS', 'Keywords'))
        ),
        $sortBy = new CompositeField(
          new HeaderField(_t('ProductSearchForm.SORTBY', 'Sort Results By'),3) ,
          new OptionsetField("sortby", "",
            array(
              //'Relevance' => _t('ProductSearchForm.RELEVANCE', 'Relevance'),
              'PageTitle' => _t('ProductSearchForm.NAME', 'Name'),
              'Category' => _t('ProductSearchForm.CATEGORY', 'Category'),
              'Location' => _t('ProductSearchForm.LOCATION', 'Location')
            ),
            'PageTitle'
          )
        )
      );

      $searchBy->ID = "ProductSearchForm_SearchBy";
      $sortBy->ID = "ProductSearchForm_SortBy";
      $categoryFields->ID = "ProductSearchForm_SearchBy_Category";
    }

    if(!$actions) {
      $actions = new FieldSet(
        new FormAction("results", _t('ProductSearchForm.SEARCH', 'Search'))
      );
    }

    parent::__construct($controller, $name, $fields, $actions);
  }

  function FormMethod() {
    return "post";
  }

  public function forTemplate(){
    return $this->renderWith(array("ProductSearchForm","AdvancedSearchForm","Form"));
  }

  /* Return dataObjectSet of the results, using the form data.
   */
  public function getResults($numPerPage = 10) {
    $invertedMatch = false;
    $data = $this->getData();
    //Debug::show($data);
    //Debug::show($_POST);

    //if($data['+']) $keywords .= " +" . ereg_replace(" +", " +", trim($data['+']));
    //if($data['quote']) $keywords .= ' "' . $data['quote'] . '"';
    //if($data['any']) $keywords .= ' ' . $data['any'];
    //if($data['-']) $keywords .= " -" . ereg_replace(" +", " -", trim($data['-']));
    $keywords = trim($data['keywords']);

    // This means that they want to just find pages where there's *no* match

    /*if($keywords[0] == '-') {
      $keywords = $data['-'];
      $invertedMatch = true;
    }*/

    $filter = '';
    // Create filters for certifications
    $cats = array();

    if ($data['ProductCategory1'] != 'Any') {
      $cats[] = (int)$data['ProductCategory1'];
    }
    if ($data['ProductCategory2'] != '') {
      $cats[] = (int)$data['ProductCategory2'];
    }
    if ($data['ProductCategory3'] != '') {
      $cats[] = (int)$data['ProductCategory3'];
    }

    if (count($cats) > 0) {
      $filter .= " CategoryID IN (" . implode(", ", $cats) . ") AND ";
    }

    // Create filter for locations
    $locationID = $data['Location'];
    if ($locationID != "") {
      $locFilter = ' ( BusinessParent.ID = ' . (int)$locationID
      . ' OR BusinessGParent.ID = ' . (int)$locationID
      . ' OR BusinessGGParent.ID = ' . (int)$locationID
      . ' OR BusinessGGGParent.ID = ' . (int)$locationID
      . ' OR BusinessGGGParent.ID = ' . (int)$locationID
      . ' ) AND ';
      $filter .= $locFilter;
    }

    $filter .= " 1=1 ";

    // Build sort by - how does relevance work??
    if($data['sortby']) {
       $sorts = array(
        'Category' => 'Category.Title ASC',
        'Location' => 'BusinessGGGParent.Title ASC, BusinessGGParent.Title ASC, BusinessGParent.Title ASC, BusinessParent.Title ASC',
        'PageTitle' => 'SiteTree_Live.Title ASC',
        /*'Relevance' => 'Relevance DESC',*/
      );
      $sortBy = $sorts[$data['sortby']] ? $sorts[$data['sortby']] : $sorts['Relevance'];
    }

    $keywords = $this->addStarsToKeywords($keywords);
    $contentFilter = $filter;

    return $this->searchEngine($keywords, $numPerPage, $sortBy, $contentFilter, true, $invertedMatch);
  }

  function getSearchQuery() {
    $data = $_REQUEST;
    $keywords = $data['keywords'];

    return trim($keywords);
  }

  /**
   * The core search engine, used by this class and its subclasses to do fun stuff.
   * Searches Products.
   */
  public function searchEngine($keywords, $numPerPage = 10, $sortBy = "Relevance DESC", $extraFilter = "", $booleanSearch = false, $invertedMatch = false) {
    /* $fileFilter = '';
     $keywords = addslashes($keywords);
     */
     if($booleanSearch) $boolean = "IN BOOLEAN MODE";
     if($extraFilter) {
       $extraFilter = " AND $extraFilter";
     }

     if($this->showInSearchTurnOn)	$extraFilter .= " AND SiteTree_Live.showInSearch <> 0";

    $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $limit = $start . ", " . (int) $numPerPage;

    $notMatch = $invertedMatch ? "NOT " : "";
    if($keywords) {
      $matchContent = "MATCH (SiteTree_Live.Title, SiteTree_Live.MenuTitle, SiteTree_Live.Content, SiteTree_Live.MetaTitle, SiteTree_Live.MetaDescription, SiteTree_Live.MetaKeywords) AGAINST ('$keywords' $boolean)";
      $matchFile = "MATCH (Filename, Title, Content) AGAINST ('$keywords' $boolean) AND ClassName = 'File'";

      // We make the relevance search by converting a boolean mode search into a normal one
      $relevanceKeywords = str_replace(array('*','+','-'),'',$keywords);
      $relevanceContent = "MATCH (SiteTree_Live.Title) AGAINST ('$relevanceKeywords') + MATCH (SiteTree_Live.Title, SiteTree_Live.MenuTitle, SiteTree_Live.Content, SiteTree_Live.MetaTitle, SiteTree_Live.MetaDescription, SiteTree_Live.MetaKeywords) AGAINST ('$relevanceKeywords')";
      $relevanceFile = "MATCH (SiteTree_Live.Filename, SiteTree_Live.Title, SiteTree_Live.Content) AGAINST ('$relevanceKeywords')";
    } else {
      $relevanceContent = $relevanceFile = 1;
      $matchContent = $matchFile = "1 = 1";
    }

    $queryContent = singleton('ProductPage')->extendedSQL($notMatch . $matchContent . $extraFilter, "");

    $baseClass = reset($queryContent->from);
    // There's no need to do all that joining
	// However - now we end up joining lots again!
    $queryContent->from = array(str_replace('`','',$baseClass) => $baseClass,
      'INNER JOIN ProductPage_Live ON SiteTree_Live.ID = ProductPage_Live.ID',
      'LEFT JOIN ProductCategory as Category ON ProductPage_Live.CategoryID = Category.ID',
      'LEFT JOIN SiteTree_Live as Business ON SiteTree_Live.ParentID = Business.ID',
      'LEFT JOIN SiteTree_Live as BusinessParent ON Business.ParentID = BusinessParent.ID', // City
      'LEFT JOIN SiteTree_Live as BusinessGParent ON BusinessParent.ParentID = BusinessGParent.ID', // Region
      'LEFT JOIN SiteTree_Live as BusinessGGParent ON BusinessGParent.ParentID = BusinessGGParent.ID', // Country
      'LEFT JOIN SiteTree_Live as BusinessGGGParent ON BusinessGGParent.ParentID = BusinessGGGParent.ID' // Continent
    );
    $queryContent->select = array("DISTINCT $baseClass.ID","$baseClass.ClassName","$baseClass.ParentID","$baseClass.Title","$baseClass.URLSegment","$baseClass.Content","$baseClass.LastEdited","$baseClass.Created","_utf8'' AS Filename", "_utf8'' AS Name", "$relevanceContent AS Relevance", "CategoryID");
    $queryContent->orderby = null;

    $fullQuery = $queryContent->sql() . " ORDER BY $sortBy LIMIT $limit";
    $totalCount = $queryContent->unlimitedRowCount();

    // die($fullQuery);
    //Debug::show($fullQuery);

    $records = DB::query($fullQuery);

    foreach($records as $record)
      $objects[] = new $record['ClassName']($record);

    if(isset($objects)) $doSet = new DataObjectSet($objects);
    else $doSet = new DataObjectSet();

    $doSet->setPageLimits($start, $numPerPage, $totalCount);
    //Debug::show($doSet);
    return $doSet;


    /*
     $keywords = preg_replace_callback('/("[^"]+")(\040or\040)("[^"]+")/', $orProcessor, $keywords);
     $keywords = preg_replace_callback('/([^ ]+)(\040or\040)([^ ]+)/', $orProcessor, $keywords);

    $limit = (int)$_GET['start'] . ", " . $numPerPage;

     $ret = DataObject::get("SiteTree", "MATCH (Title, MenuTitle, Content, MetaTitle, MetaDescription, MetaKeywords) "
           ."AGAINST ('$keywords' IN BOOLEAN MODE) AND `ShowInSearch` = 1","Title","", $limit);

     return $ret;
    */
  }

}
