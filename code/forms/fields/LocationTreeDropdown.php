<?php
/**
 * Create a dropdown from all instances of a class.
 *
 * @package forms
 * @subpackage fields-relational
 *
 * @deprecated 2.3 Misleading naming
 */
class LocationTreeDropdown extends DropdownField {
  
  /**
   * @var string $titleFieldName The name of the DataObject property used for the dropdown options
   */
  protected $titleFieldName = "Title";
  
  /**
   * @var string $prefixChar The character added to options prefix for each level of heirachy
   */
  protected $prefixChar = '-';
  
  /**
   * @param string $name
   * @param string $title
   * @param string $className 
   */
  function __construct( $name, $title, $value = null, $form = null, $currentID = null, $currentTitle = null) {
    
    $optionArray = $this->_getOptions();
    
    $extraFields = array(
			array(
				'value'=>'any',
				'title'=>'Any Location'
			)
		);
    $optionArray = array_merge($extraFields,$optionArray);
    //Debug::show($optionArray);
  
    parent::__construct( $name, $title, $optionArray, $value, $form, null );
  }

  function setTitleFieldName($name) {
    $this->titleFieldName = $name;
  }
  
  private function _getOptions( $obj = null , $prefix = '') {
    if(!$obj) {
      $parent = DataObject::get_one('BrowseWorldPage');
    } else {
      $parent = $obj;
    }
    //Debug::show($parent);
    $children = $parent->Children();
    $locations = array();
    if (get_class($children) == 'DataObjectSet') {
      foreach ($children as $child) {
        //Debug::show($child);
        if( $child->ClassName == 'BrowseContinentsPage' ||
            $child->ClassName == 'BrowseCountriesPage' /*||
            $child->ClassName == 'BrowseRegionsPage'*/ )
        {
          $businessCount = $child->getBusinessCount();
          $locations[] = array( 'value' => $child->ID, 'title' => "$prefix " . $child->{$this->titleFieldName} . " ($businessCount)" );
          $childLocations = $this->_getOptions($child, $prefix.$this->prefixChar);
          if (is_array($childLocations)){
            $locations = array_merge($locations,$childLocations);
          }
          //Debug::show($locations);
        }
      }
    } else {
      //Debug::message("No children or only 1 child??");
      //Debug::show($parent);
      //Debug::show($children);
    }
    //Debug::show($businesses);
    return $locations;
  }
  
  /**
	 * Returns a <select> tag containing all the appropriate <option> tags
	 */
	function Field() {
		$classAttr = '';
		$options = '';
		if($extraClass = trim($this->extraClass())) {
			$classAttr = "class=\"$extraClass\"";
		}
		$selectedYet = false;
		if($this->source) foreach($this->source as $s) {
		  $value = $s['value'];
		  $title = $s['title'];
			$selected = ($value == $this->value && !$selectedYet) ? " selected=\"selected\"" : "";
			if($selected && $this->value != 0) {
			  $selectedYet = true;
				$this->isSelected = true;
			}
			$options .= "<option$selected value=\"$value\">$title</option>";
		}
	
		$id = $this->id();
		$disabled = $this->disabled ? " disabled=\"disabled\"" : "";
		
		return "<select $classAttr $disabled name=\"$this->name\" id=\"$id\">$options</select>";
	}
}
?>