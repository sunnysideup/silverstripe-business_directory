<?php

/**
 * @package members_directory
 */

/**
 * More advanced search form
 * @package business_directory
 */
class BusinessSearchForm extends SearchForm {

  /**
   * the constructor of a Simple/basic SearchForm
   */
  function __construct($controller, $name, $fields = null, $actions = null) {
    // Need this before we call parent::_construct
    $this->controller = $controller;

    if(!$fields) {

			if ($this->controller->GeoLevelName() != 'No level' && $this->controller->getBusinessCount() != 0) {
				$searchBusinessHeading = 'Search Businesses in '.$this->controller->Title;
			} else {
				$searchBusinessHeading = _t('BusinessSearchForm.SEARCHBUSINESSES','Search Our Network');
			}

      $fields = new FieldSet(
        $searchBy = new CompositeField(
          new HeaderField($searchBusinessHeading,2),
          new HeaderField(_t('BusinessSearchForm.BUSINESSDETAILS', 'Business Details'),4),
					new NumericField("floid", _t('BusinessSearchForm.FLOID', 'FLO ID')),
          new TextField("keywords", _t('BusinessSearchForm.KEYWORDS', 'Name or keyword')),
          $this->getLocationFields(),
          $certFields = new CompositeField (
						new HeaderField(_t('BusinessSearchForm.SEARCHCRITERIA', 'Product Type / Function'),4),
						new MyTypeDropdown("ProductCategory1", 'Product Type', 'ProductCategory',null,null,false,array('All'=>'All')),
            new MyTypeDropdown("CertificationType1", 'Function', 'CertificationType',null,null,false,array('All'=>'All')),
            new LiteralField('andOr1','<span class="andOr" id="andOr1">or</span>'),
            new MyTypeDropdown("ProductCategory2", 'Product Type', 'ProductCategory',null,null,false,array('All'=>'All')),
            new MyTypeDropdown("CertificationType2", 'Function', 'CertificationType',null,null,false,array('All'=>'All')),
            new LiteralField('andOr2','<span class="andOr" id="andOr2">or</span>'),
            new MyTypeDropdown("ProductCategory3", 'Product Type', 'ProductCategory',null,null,false,array('All'=>'All')),
            new MyTypeDropdown("CertificationType3", 'Function', 'CertificationType',null,null,false,array('All'=>'All')),
            new LiteralField('addCriteria','<div id="addRemoveCriteria"><a href="#" id="addCriteria">Add criteria</a><span id="addRemovePipe"> | </span><a href="#" id="removeCriteria">Remove criteria</a></div>'),
						new OptionsetField('CertificationAndOr','',array('AND' => 'Must match ALL of the selection below','OR' => 'Must match ANY of the selection below'),'AND')
          )

        ),
				$sortBy = new HiddenField('sortby','','Location')
        /*$sortBy = new CompositeField(
          new LiteralField('showhidesortby','<h4><a href="#" id="showhidesortby">Sort Results By</a></h4>') ,
          new OptionsetField("sortby", "",
            array(
              'PageTitle' => _t('BusinessSearchForm.NAME', 'Name'),
              'Location' => _t('BusinessSearchForm.REGION', 'Region')
            ),
            'Location'
          )
        )*/
      );

      $searchBy->ID = "BusinessSearchForm_SearchBy";
      $sortBy->ID = "BusinessSearchForm_SortBy";
      $certFields->ID = "BusinessSearchForm_SearchBy_CertificationCriteria";
    }

    if(!$actions) {
      $actions = new FieldSet(
        new FormAction("results", _t('BusinessSearchForm.SEARCH', 'Search'))
      );
    }

    parent::__construct($controller, $name, $fields, $actions);
  }

  function FormMethod() {
    return "get";
  }

  public function forTemplate(){
    return $this->renderWith(array("BusinessSearchForm","AdvancedSearchForm","Form"));
  }

  /* Return dataObjectSet of the results, using the form data.
   */
  public function getResults($numPerPage = 10) {
		$invertedMatch = false;
    $data = $this->getData();

    $keywords = (isset($data['keywords']) ? trim($data['keywords']) : false);
    $floid = (isset($data['floid']) ? trim($data['floid']) : false);

    if ($floid != "" && (int)$floid != 0) {
      $filter = 'FLOID = '.(int)$floid;
      $keywords = "";
    } else {
			// This means that they want to just find pages where there's *no* match

	    if(substr($keywords,0,1) == '-') {
	      $keywords = $data['-'];
	      $invertedMatch = true;
	    } else {
	    	$invertedMatch = false;
			}

	    switch ($data['CertificationAndOr']) {
	      case 'AND':
	        $certAndOr = "AND";
	      break;
	      case 'OR':
	      default:
	        $certAndOr = "OR";
	      break;
	    }

	    $filter = '';
	    // Create filters for certifications
	    $sq = array();
	    $sq1 = $this->buildCertificationSubquery($data['ProductCategory1'], $data['CertificationType1']);
	    $sq2 = $this->buildCertificationSubquery($data['ProductCategory2'], $data['CertificationType2']);
	    $sq3 = $this->buildCertificationSubquery($data['ProductCategory3'], $data['CertificationType3']);

	    if ($sq1 || $sq1 || $sq1) {
	      $filter .= " ( ";

	      if ($sq1) {
	        $sq[] = "Certification.ID IN ($sq1)";
	      }
	      if ($sq2) {
	        $sq[] = "Certification.ID IN ($sq2)";
	      }
	      if ($sq3) {
	        $sq[] = "Certification.ID IN ($sq3)";
	      }

	      $filter .= implode(" $certAndOr ", $sq);
	      $filter .= " ) AND ";
	    }

	    $locationID = $data['Location'];
	    if ($locationID != "" && $locationID != "any") {
	      if (is_array($locationID)) {
	        foreach ($locationID as $k => $l) {
	          $locationID[$k] = (int)$l;
	        }
	      	$locFilter = ' ( BusinessParent.ID IN (' . implode(',',$locationID)
		      . ') OR BusinessGParent.ID IN (' . implode(',',$locationID)
		      . ') OR BusinessGGParent.ID IN (' . implode(',',$locationID)
		      . ') OR BusinessGGGParent.ID IN (' . implode(',',$locationID)
		      //. ') OR BusinessGGGGParent.ID IN (' . implode(',',$locationID)
		      . ') ) AND ';
		      $filter .= $locFilter;
	      } else {
		      $locFilter = ' ( BusinessParent.ID = ' . (int)$locationID
		      . ' OR BusinessGParent.ID = ' . (int)$locationID
		      . ' OR BusinessGGParent.ID = ' . (int)$locationID
		      . ' OR BusinessGGGParent.ID = ' . (int)$locationID
		      //. ' OR BusinessGGGGParent.ID = ' . (int)$locationID
		      . ' ) AND ';
		      $filter .= $locFilter;
	      }
	    }

	    $filter .= " 1=1 ";
    }

    // Build sort by - how does relevance work??
    if($data['sortby']) {
       $sorts = array(
        //'Type' => 'Certification.ID ASC',
        'Location' => 'BusinessParent.Title ASC, BusinessGParent.Title ASC, BusinessGGParent.Title ASC, BusinessGGGParent.Title ASC',
        'PageTitle' => 'Title ASC',
        //'Relevance' => 'Relevance DESC',
      );
      $sortBy = $sorts[$data['sortby']] ? $sorts[$data['sortby']] : $sorts['Relevance'];
    }

    $keywords = $this->addStarsToKeywords(addslashes($keywords));
    $contentFilter = $filter;

    return $this->searchEngine($keywords, $numPerPage, $sortBy, $contentFilter, true, $invertedMatch);
  }

  function getSearchQuery() {
    $data = $_REQUEST;
    $keywords = trim($data['keywords']);
    $floid = trim($data['floid']);
    $andOr = $data['CertificationAndOr'];

    if ($floid != "" && (int)$floid != 0) {
      $record = array('FLOID' => (int)$floid);
      $query = new ArrayData($record);
    } else {
      $certs = new DataObjectSet();
      for($i = 1; $i <= 3; $i++) {
				$ProductCategory = false;
				$CertType = false;
		    $pcID = $data["ProductCategory".$i];
		    $typeID = $data["CertificationType".$i];

		    if ($pcID != "" && $pcID != "All") {
		      $ProductCategory = DataObject::get_by_id('ProductCategory',(int)$pcID);
		    }
		    if ($typeID != "" && $typeID != "All") {
		      $CertType = DataObject::get_by_id('CertificationType',(int)$typeID);
		    }
		    if( $ProductCategory || $CertType ) {
		      $cert = array(
		        'ProductCategory' => $ProductCategory,
		        'CertType' => $CertType,
		      );
		      $certs->push(new ArrayData($cert));
		    }
		  }


      $locations = new DataObjectSet();

      $locationID = $data['Location'];
	    if ($locationID != "" && $locationID != "any") {
	      if (is_array($locationID)) {
	        foreach ($locationID as $k => $l) {
	          $locations->push(DataObject::get_by_id('BrowseAbstractPage',(int)$l));
	        }
				} else {
		      $locations->push(DataObject::get_by_id('BrowseAbstractPage',(int)$locationID));
				}
			}

			$record = array(
				'keywords' => $keywords,
				'certs' => $certs,
				'locations' => $locations,
				'andor' => strtolower($andOr)
			);
			$query = new ArrayData($record);
    }
    //Debug::show($query);

    return $query;
  }

  /**
   * The core search engine, used by this class and its subclasses to do fun stuff.
   * Searches Businesses.
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

    $queryContent = singleton('BusinessPage')->extendedSQL($notMatch . $matchContent . $extraFilter, "");

    $baseClass = reset($queryContent->from);
    // There's no need to do all that joining
	// However - now we end up joining lots again!
    $queryContent->from = array(str_replace('`','',$baseClass) => $baseClass,
      'INNER JOIN BusinessPage_Live ON SiteTree_Live.ID = BusinessPage_Live.ID',
      'LEFT JOIN BusinessPage_Certifications ON BusinessPage_Live.ID = BusinessPage_Certifications.BusinessPageID',
      'LEFT JOIN Certification ON Certification.ID = BusinessPage_Certifications.CertificationID ',
      'LEFT JOIN SiteTree_Live as BusinessParent ON SiteTree_Live.ParentID = BusinessParent.ID', // City
      'LEFT JOIN SiteTree_Live as BusinessGParent ON BusinessParent.ParentID = BusinessGParent.ID', // Region
      'LEFT JOIN SiteTree_Live as BusinessGGParent ON BusinessGParent.ParentID = BusinessGGParent.ID', // Country
      'LEFT JOIN SiteTree_Live as BusinessGGGParent ON BusinessGGParent.ParentID = BusinessGGGParent.ID' // Continent
    );
    $queryContent->select = array("DISTINCT $baseClass.ID","$baseClass.ClassName","$baseClass.ParentID","$baseClass.Title","$baseClass.URLSegment","$baseClass.Content","$baseClass.LastEdited","$baseClass.Created","_utf8'' AS Filename", "_utf8'' AS Name", "$relevanceContent AS Relevance");
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
    return $doSet;
  }

  private function buildCertificationSubquery($prodCat, $certType) {
    if (($prodCat != "" && $prodCat != "All") || ($certType != "" && $certType != "All")) {
    	$certSubQuery = '';
      $certSubQuery .= 'SELECT Certification.ID FROM Certification WHERE 1=1 ';
      if ($prodCat != "" && $prodCat != 'All')
        $certSubQuery .= ' AND ProductCategoryID = '.(int)$prodCat;
      if ($certType != "" && $certType != 'All')
        $certSubQuery .= ' AND CertificationTypeID = '.(int)$certType;

			//Start hack
			/*$certs = DataObject::get('Certification','1=1'.$certSubQuery);
			$certSubQuery = false;
			if ($certs){
				$certIds = array();
				foreach ($certs as $cert)
					$certIds[] = $cert->ID;
				if (count($certIds) > 0)
					$certSubQuery = implode(',',$certIds);
			}*/
			//End hack
    } else {
      $certSubQuery = false;
    }
    return $certSubQuery;
  }

  private function getLocationFields() {
    $children = $this->controller->Children();

    if($children) {
      $children->sort('Title');
      $locations = array();
      $values = array();
      foreach ($children as $child) {
        if ( get_class($child) == 'BrowseContinentsPage' ||
						get_class($child) == 'BrowseCountriesPage' ||
						get_class($child) == 'BrowseRegionsPage' ||
						get_class($child) == 'BrowseCitiesPage' ) {
          $businessCount = $child->getBusinessCount();
					if ($businessCount != 0 ) {
        		$locations[$child->ID] = $child->Title . " ($businessCount)";
        		$values[] = $child->ID;
        		$geoLevel = $child->GeoLevelName();
					}
				}
      }
      if (is_array($locations) && count($locations) != 0){
        if ($this->controller->GeoLevelName() != 'No level') {
          $heading = 'Select '.$geoLevel.' in '.$this->controller->Title;
        } else {
          $heading = 'Select '.$geoLevel;
        }
      	return new CompositeField(new HeaderField($heading,4),
							 new CheckboxSetField('Location','',$locations, $values));
			} else {
			  return new CompositeField(new HeaderField("Search within",4),
							 new LocationTreeDropdown('Location','',null,$this,$this->controller->ID,$this->controller->Title));
			}
    } else {
    	return new CompositeField(new HeaderField("Search within",4),
						 new LocationTreeDropdown('Location','',null,$this,$this->controller->ID,$this->controller->Title));
    }
  }

}
