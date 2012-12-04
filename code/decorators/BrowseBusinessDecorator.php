<?php

class BrowseBusinessDecorator extends SiteTreeDecorator {

	protected static $classes_that_can_have_businesses_as_children = array();
		static function set_classes_that_can_have_businesses_as_children($a) {self::$classes_that_can_have_businesses_as_children = $a;}
		static function get_classes_that_can_have_businesses_as_children() {return self::$classes_that_can_have_businesses_as_children;}

	public function extraStatics() {
		return array (
			'many_many' => array(
				'BusinessPages' => 'BusinessPage'
			)
		);
	}


	public function getParentIDs() {
		$array = array();
		if($this->owner->ParentID) {
			$array[$this->owner->ParentID] = $this->owner->ParentID;
			$parent = DataObject::get_by_id("SiteTree", $this->owner->ParentID);
			$parentArray = $parent->getParentIDs();
			if(count($parentArray)) {
				$array = array_merge($array, $parentArray);
			}
		}
		return $array;
	}

	public function BusinessList(){
		$array = $this->getChildIDs();
		if($array && count($array)) {
			$extension = '';
			if(Versioned::current_stage() == "Live") {
				$extension = "_Live";
			}
			return DataObject::get("BusinessPage", "\"SiteTree$extension\".\"ID\" IN (".implode(",", $array).")");
		}
	}

	public function getChildIDs() {
		$array = array();
		if($children = DataObject::get("SiteTree", "ParentID = ".$this->owner->ID)) {
			foreach($children as $child) {
				if($child instanceOf BusinessPage) {
					$array[$child->ID] = $child->ID;
				}
				else {
					$childChildArray = $child->getChildIDs();
					if(count($childChildArray)) {
						$array = array_merge($array, $childChildArray);
					}
				}
			}

		}
		return $array;
	}

	function onBeforeWrite() {
		$array = array();
		$field = '';
		$otherField = '';
		if($this->owner instanceOf BusinessPage) {
			$field = "BusinessPageID";
			$otherField = "SiteTreeID";
			$array = $this->getParentIDs();
		}
		elseif(in_array($this->owner->ClassName, self::get_classes_that_can_have_businesses_as_children())) {
			$field = "SiteTreeID";
			$otherField = "BusinessPageID";
			$array = $this->owner->getChildIDs();
		}
		if($field) {
			if(count($array)) {
				DB::query("DELETE FROM \"SiteTree_BusinessPages\" WHERE \"$field\" = ".$this->owner->ID);
				foreach($array as $fieldID) {
					DB::query("INSERT INTO \"SiteTree_BusinessPages\" (\"$field\" , \"$otherField\") VALUES ('".$this->owner->ID."', '$fieldID');");
				}
			}
		}
	}


}

class BrowseBusinessDecorator_Controller extends Extension {


	function filterforproductcategory(){

	}

	function filterforcategory() {

	}

}


