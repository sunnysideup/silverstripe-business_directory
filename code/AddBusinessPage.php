<?php

/**
 * Steps:
 *
 * 1. go to page
 * 2. enter name and location
 * 3. creatge page 
 * 4. allow edits (move marker on map, edit details)
 *
 * MEMBERSHIP
 * Each page needs to be confirmed with an email click.  Before this is done, the page
 * "ShowInSearch" + ShowInMenus = false
 * Each person creates an account, if they are not logged in yet.
 * If they try to create an account with an existing email then they are asked to log in.
 *
 * LOCATION
 * It is possible to go to this page with preset variables (e.g. they did a search and there is nothing there...)
 * You can only come from this page from the "future" parent page.  
 *
 **/



class AddBusinessPage extends Page {

	/**
	 * Standard SS static
	 **/ 
	static $icon = "business_directory/images/treeicons/AddBusinessPage";

	function canCreate($member = null) {
		return !DataObject::get_one("AddBusinessPage", "\"ClassName\" = 'AddBusinessPage'");
	}

	function getCMSFields( $cms ) {
		$fields = parent::getCMSFields( $cms );
		$fields->addFieldToTab("Root.Content.Links", new TreeDropdownField("SearchPageID", "Search Page (in case no parent page is provided)", "SiteTree"));
		return $fields;
	}	

}

class AddBusinessPage_Controller extends Page_Controller {

	protected $parentPageID = 0;

	public function NewListingForm() {
		$address = '';
		if(isset($_GET["address"])) {
			$address = $_GET["address"];
		}
		$businessName = '';
		if(isset($_GET["name"])) {
			$businessName = $_GET["name"];
		}
		$fields = new FieldSet();
		$fields->push(new TextField($name = "NewListingName", $title = "New Listing Name", $businessName));
		$fields->push(new AddressFinderField($name = "NewListingAddress", $title = "Address / Location for listing", $address));
		$fields->push(new HiddenField($name = "ParentPageID", $this->parentPageID));
		$validator = new RequiredFields("NewListingName", "NewListingAddress");
		$actions = new FieldSet (
			new FormAction("donewlistingform", "Create Listing")
		);
		$form = new Form($this->owner, "newlistingform", $fields, $actions, $validator );
		if(isset($_GET["error"]) && $_GET["error"]) {
			$form->setMessage($_GET["error"],"bad");
		}
		return $form;
	}

	public function donewlistingform($data = null, $form = null) {
		//add new search record here....
		if(!isset($data["NewListingAddress"])) {$data["NewListingAddress"] = '';}
		if(!isset($data["NewListingName"])) {$data["NewListingName"] = '';}
		$extension = '';
		if(Versioned::current_stage() == "Live") {
			$extension = "_Live";
		}
		if(
			DataObject::get_one("SiteTree", "\"SiteTree{$extension}\".\"Title\" = '".Convert::raw2sql($data["NewListingName"])."'") ||
			!$data["NewListingName"] ||
			strlen($data["NewListingName"]) < 3
		) {
			$form->addErrorMessage('NewListingName','Sorry, but a listing with that name already exists.', "bad");
			Director::redirectBack();
			return;
		}
		$addressArray = $form->dataFieldByName("NewListingAddress")->getAddressArray();
		if($addressArray) {
			if(GoogleMapLocationsObject::pointExists($addressArray)) {
				$form->addErrorMessage('NewListingAddress','This address already exists for another listing.  Please check the existing listings first to prevent double-ups OR use a more specific address.', "bad");
				Director::redirectBack();
				return;
			}
			$parentPage = BrowseCitiesPage::get_clostest_city_page($addressArray);
			if(!$parentPage) {
				$form->addErrorMessage('NewListingAddress','Location could not be found. ', "bad");
				Director::redirectBack();
				return;
			}
			$nextLink = $this->linkWithExtras(array("parent" => $parentPage->ID, "address" => $addressArray, "name" => $data["NewListingName"]), 'createnewbusinesslistingfrompoint');
			if(!Member::currentMember()) {
				$nextLink = RegisterAndEditDetailsPage::link_for_going_to_page_via_making_user($nextLink);
			}
			Director::redirect($nextLink);
			return;
		}
		$nextLink = $this->linkWithExtras(array("address" => $data["NewListingAddress"], "name" => $data["NewListingName"]));
		Director::redirect($this->backwardLink);
		return;
	}

	function createnewbusinesslistingfrompoint($request) {
		if(!isset($_GET["address"])) {$addressArray = array(); $address = '';} else {$addressArray = unserialize($_GET["address"]); $address = $addressArray["address"];}
		if(!isset($_GET["name"])) {$name = '';} else {$name = Convert::raw2xml($_GET["name"]);}
		if(!isset($_GET["parent"])) {$parent = 0;} else {$parent = intval($_GET["parent"]);}
		if($member = Member::currentMember()) {
			if($name) {
				if($parent && $parentPage = DataObject::get_by_id("SiteTree", $parent)) {
					if($address && count($addressArray)) {
						$allowedParents = BusinessPage::get_can_be_child_off();
						if(is_array($allowedParents) && in_array($parentPage->ClassName, $allowedParents)) {
							$extension = '';
							if(Versioned::current_stage() == "Live") {
								$extension = "_Live";
							}							
							$page = DataObject::get_one("BusinessPage", "ParentID = ".$parentPage->ID." AND SiteTree{$extension}.Title = '".Convert::raw2sql($name)."'");
							if($page ) {
								//do nothing
							}
							else {
								$page = new BusinessPage();
								$page->Title = $name;
								$page->MenuTitle = $name;
								$page->MetaTitle = $name;
								$page->Email = $member->Email;
								$page->ParentID = $parentPage->ID;
								$page->writeToStage('Stage');
								$page->publish('Stage', 'Live');
								$page->flushCache();
								$page->Members()->add($member);
								$member->addToGroupByCode(BusinessPage::get_member_group_code());
							}
							$point = new GoogleMapLocationsObject();
							$point->addDataFromArray($addressArray);
							$point->ParentID = $page->ID;
							$point->write();
							Director::redirect($page->Link());
							return;
						}
						else {
							Director::redirect($this->linkWithExtras(array("address" => $address, "name" => $name, "error" => "Could not find correct parent page type " )));
							return;
						}
					}
					else {
						Director::redirect($this->linkWithExtras(array("address" => $address, "name" => $name, "error" => "Could not find address page." )));
						return;
					}
				}
				else {
					Director::redirect($this->linkWithExtras(array("address" => $address, "name" => $name, "error" => "Could not find parent page.")));
					return;
				}
			}
			Director::redirect($this->linkWithExtras(array("address" => $address, "name" => $name, "error" => "Could not find listing name.")));
			return;
		}
		else {
			Security::permissionFailure($this, "You must have an account and be logged in to create new a new listing.");
		}
	}

	protected function linkWithExtras($getVarArray, $action  = '') {
		$getString = '';
		foreach($getVarArray as $key => $value) {
			if(is_array($value)) {
				$value = serialize($value);
				$getVarArray[$key] = $value;
			}
			$getString .= "&$key=".urlencode($value);
		}
		if(!$action) {
			$action = '';
		}
		return $this->Link($action)."?$getString";
	}

	
}
