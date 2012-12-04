<?php
/**
 * manage listings and related items
 *
 *
 *
 */


class BusinessListingMananger extends ModelAdmin{

	public static $url_segment = 'listings';

	public static $menu_title = 'Listings';

	public static $menu_priority = 3;

	//static $url_priority = 50;

	public static $managed_models = array('BusinessPage', 'CertificationPage', 'ProductCategoryPage', 'OpeningHour');
		public static function set_managed_models(array $array) {self::$managed_models = $array;}
		public static function add_managed_model($s) {self::$managed_models[] = $s;}
		public static function remove_managed_model($s) {
			if(self::$managed_models && count(self::$managed_models)){
				foreach(self::$managed_models as $key => $model) {
					if($model == $s) {
						unset(self::$managed_models[$key]);
					}
				}
			}
		}

	public static $collection_controller_class = 'BusinessListingMananger_CollectionController';

	public static $record_controller_class = 'BusinessListingMananger_RecordController';

	function init() {
		parent::init();
	}


	/**
	 *
	 *@return String (URLSegment)
	 **/
	function urlSegmenter() {
		return self::$url_segment;
	}
}


class BusinessListingMananger_CollectionController extends ModelAdmin_CollectionController {


	function search($request, $form) {
		// Get the results form to be rendered
		$query = $this->getSearchQuery(array_merge($form->getData(), $request));
		$resultMap = new SQLMap($query, $keyField = "ID", $titleField = "Title");
		$items = $resultMap->getItems();
		$array = array();
		if($items && $items->count()) {
			foreach($items as $item) {
				$array[] = $item->ID;
			}
		}
		Session::set("BusinessListingMananger".$this->modelClass,serialize($array));
		return parent::search($request, $form);
	}

	function urlSegmenter() {
		return self::$url_segment;
	}
}

class BusinessListingMananger_RecordController extends ModelAdmin_RecordController {


	protected static $actions_to_keep = array(
		"Back",
		"doDelete",
		"doSave"
	);


	/**
	 * Returns a form for editing the attached model
	 *
	 *@return Form
	 **/
	public function EditForm() {
		$form = parent::EditForm();
		if($this->currentRecord instanceof SiteTree){
			$oldActions = $form->Actions();
			//in order of appearance
			//$form->unsetActionByName("action_doDelete"); - USEFUL TO KEEP
			$form->unsetActionByName("action_unpublish");
			$form->unsetActionByName("action_delete");
			$form->unsetActionByName("action_save");
			$form->unsetActionByName("action_publish");
			//$form->unsetActionByName("action_doSave"); - USEFUL TO KEEP
			$actions = $form->Actions();
			$actions->push(new FormAction("doGoto", "go to page"));
			$form->setActions($actions);
		}
		if($this->parentController) {
			$array = unserialize(Session::get("BusinessListingMananger".$this->parentController->getModelClass()));
			if(is_array($array)) {
				if(count($array) && count($array) > 1) {
					foreach($array as $key => $id) {
						if($id == $this->currentRecord->ID) {
							if(isset($array[$key + 1]) && $array[$key + 1]) {
								$nextRecordID = $array[$key + 1];
								$nextRecordURL = $this->parentController->Link(Controller::join_links($nextRecordID, "edit"));
								$form->Actions()->push(new FormAction("goNext", "Next Record"));
								$form->Fields()->push(new HiddenField("nextRecordURL", null, $nextRecordURL));
							}
							if(isset($array[$key - 1]) && $array[$key - 1]) {
								$prevRecordID = $array[$key - 1];
								$nextRecordURL = $this->parentController->Link(Controller::join_links($prevRecordID, "edit"));
								$form->Actions()->insertFirst(new FormAction("goPrev", "Previous Record"));
								$form->Fields()->push(new HiddenField("prevRecordURL", null, $nextRecordURL));
							}
						}
					}
				}
			}
		}
		return $form;
	}


	/**
	 * Postback action to save a record
	 *
	 * @param array $data
	 * @param Form $form
	 * @param SS_HTTPRequest $request
	 * @return mixed
	 */
	function doSave($data, $form, $request) {
		$form->saveInto($this->currentRecord);
		if($this->currentRecord instanceof SiteTree){
			try {
				$this->currentRecord->writeToStage("Stage");
				$this->currentRecord->publish("Stage", "Live");
			}
			catch(ValidationException $e) {
				$form->sessionMessage($e->getResult()->message(), 'bad');
			}
		}
		else {
			try {
				$this->currentRecord->write();
			}
			catch(ValidationException $e) {
				$form->sessionMessage($e->getResult()->message(), 'bad');
			}
		}

		// Behaviour switched on ajax.
		if(Director::is_ajax()) {
			return $this->edit($request);
		}
		else {
			Director::redirectBack();
		}
	}

	function doGoto($data, $form, $request) {
		Director::redirect($this->currentRecord->Link());
	}


}
