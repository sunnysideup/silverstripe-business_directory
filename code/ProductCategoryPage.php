<?php
/**
 * ProductPage.php: Sub-class of Page
 * Contains info about a product
 * @created 14/10/2008
 */

class ProductCategoryPage extends Page
{

    /**
     *Standard SS Static
     **/
    public static $icon = "business_directory/images/treeicons/ProductCategoryPage";

    /**
     *Standard SS Static
     **/
    public static $has_one = array(
        "Image" => "Image"
    );

    /**
     *Standard SS Static
     **/
    public static $can_be_root = false;

    /**
     *Standard SS Static
     **/
    public static $belongs_many_many = array(
        'Businesses' => 'BusinessPage'
    );

    public function getCMSFields($cms)
    {
        $fields = parent::getCMSFields($cms);
        $fields->addFieldToTab("Root.Content.Logo", new ImageField("Image", "Image", $value = null, $form = null, $rightTitle = null, $folderName = "/assets/ProductCategoryImages/"));
        return $fields;
    }
}

class ProductCategoryPage_Controller extends Page_Controller
{
}
