<?php
/**
 * ProductPage.php: Sub-class of Page
 * Contains info about a product
 * @created 14/10/2008
 */

class CertificationPage extends Page
{

    /**
     *Standard SS Static
     **/
    public static $icon = "business_directory/images/treeicons/CertificationPage";

    /**
     *Standard SS Static
     **/
    public static $db = array(
        "Website" => "Varchar(255)",
    );

    /**
     *Standard SS Static
     **/
    public static $has_one = array(
        "Logo" => "Image"
    );

    /**
     *Standard SS Static
     **/
    public static $belongs_many_many = array(
        'Businesses' => 'BusinessPage'
    );


    /**
     *Standard SS Static
     **/
    public static $can_be_root = false;

    /**
     *Standard SS Method
     **/
    public function getCMSFields($cms)
    {
        $fields = parent::getCMSFields($cms);
        $members = DataObject::get('Member');
        if ($galleries) {
            $galleries = $galleries->toDropdownMap('ID', 'Title', '(Select one)', true);
        }
        $fields->addFieldToTab('Root.Content.Main', new DropdownField('GalleryID', 'Gallery', $galleries), 'Content');
        $fields->addFieldToTab("Root.Content.MoreInfo", new TextField("Website"));
        $fields->addFieldToTab("Root.Content.Logo", new ImageField("Logo", "Logo", $value = null, $form = null, $rightTitle = null, $folderName = "/assets/CertificationLogos/"));
        return $fields;
    }

    /**
     * returns the number of businesses that carry this certification.
     *@return Integer
     **/
    public function NumberOfBusinesses()
    {
        return $this->Businesses()->count();
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
    }
}

class CertificationPage_Controller extends Page_Controller
{
}
