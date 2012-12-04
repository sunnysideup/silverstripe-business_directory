<?php
/**
 * Create a dropdown from all instances of a class.
 *
 * @package forms
 * @subpackage fields-relational
 *
 * @deprecated 2.3 Misleading naming
 */
class MyTypeDropdownMultiselect extends MyTypeDropdown {
  
  /**
   * @var string $titleFieldName The name of the DataObject property used for the dropdown options
   */
  protected $titleFieldName = "Title";

  /**
   * @param string $name
   * @param string $title
   * @param string $className 
   */
  function __construct( $name, $title, $className, $value = null, $form = null, $emptyString = null, $extraFields = null, $size = 4) {
    $this->size = $size;

    parent::__construct( $name, $title, $className, $value, $form, $emptyString, $extraFields );
  }
  
  /**
   * Returns a <select> tag containing all the appropriate <option> tags
   * Overriding this to make it multiselect & use size property
   */
  function Field() {
    $classAttr = '';
    $options = '';
    if($extraClass = trim($this->extraClass())) {
      $classAttr = "class=\"$extraClass\"";
    }
    if($this->source) foreach($this->source as $value => $title) {
      $selected = $value == $this->value ? " selected=\"selected\"" : "";
      if($selected && $this->value != 0) {
        $this->isSelected = true;
      }
      $options .= "<option$selected value=\"$value\">$title</option>";
    }
  
    $id = $this->id();
    $disabled = $this->disabled ? " disabled=\"disabled\"" : "";
    
    return "<select $classAttr $disabled name=\"$this->name\" id=\"$id\" size=\"$this->size\" multiple=\"multiple\">$options</select>";
  }
}
?>