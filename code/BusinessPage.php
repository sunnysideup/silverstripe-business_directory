<?php
/**
 * BusinessPage.php: Sub-class of Page
 * Contains info about a business
 * @created 14/10/2008
 */

class BusinessPage extends Page {

	/**
	 * Standard SS static
	 **/
	static $icon = "business_directory/images/treeicons/BusinessPage";

	/**
	 * Standard SS static
	 **/
	static $db = array (
		"IntroParagraph" => "Text",
		"Phone" => "Varchar(100)",
		"Email" => "Varchar(100)",
		"Skype" => "Varchar(100)",
		"IM" => "Varchar(100)",
		"ListingEmail" => "Varchar(100)",
		"MailingAddress" => "Text",
		"Notes" => "Text",
		"OpeningHoursNote" => "Text",
		"Website" => "Varchar(200)",
		"SocialMediaLink1" => "Varchar(255)",
		"SocialMediaLink2" => "Varchar(255)",
		"FirstName" => "Varchar(255)",
		"LastName" => "Varchar(255)",
		"AlternativeContactDetails" => "Text",
		"OrganisationDescription" => "Text",
		"ReasonForFounding" => "Text",
		"LastEmailSent" => "SSDatetime"
	);

	/**
	 * Standard SS static
	 **/
	static $has_one = array (
		'Image1' => 'Image',
		'Image2' => 'Image',
		'Image3' => 'Image'
	);

	/**
	 * Standard SS static
	 **/
	static $has_many = array (
		'OpeningHours' => 'OpeningHour'
	);



	/**
	 * Standard SS static
	 **/
	static $many_many = array (
		'Certifications' => 'CertificationPage',
		'ProductCategories' => 'ProductCategoryPage',
		'Members' => 'Member'
	);

	/**
	 * Standard SS static
	 **/
	public static $many_many_extraFields = array(
		'Certifications' => array(
			'CertificationYear' => 'Int',
			'CertificationCode' => 'Text'
		)
	);


	/**
	 * Standard SS static
	 **/
	static $can_be_root = false;

	/**
	 *permissions and actions
	 * List of permission codes a user can have to allow a user to create a page of this type.
	 **/
 	static $need_permission = array(
		'ADMIN',
		'CMS_ACCESS_BusinessAdmin',
		'ACCESS_FORUM',
		'ACCESS_BUSINESS'
	);


	/**
	 * Standard SS static
	 **/
	static $defaults = array (
		"HasGeoInfo" => 1,
		"ProvideComments" => true
	);

	/**
	 * Standard SS static
	 **/
	static $allowed_children = array("ProductPage");

	/**
	 * Standard SS static
	 **/
	static $default_child = "ProductPage";

	/**
	 * CODE of the group that business page admins are put in.
	 **/
	protected static $member_group_code = "listing-member";
		static function get_member_group_code() {return self::$member_group_code;}
		static function set_member_group_code($s) {self::$member_group_code = $s;}
	/**
	 * NAME of the group that business page admins are put in.
	 **/
	static $member_group_title = "Business Members";
		static function get_member_group_title() {return self::$member_group_title;}
		static function set_member_group_title($s) {self::$member_group_title = $s;}

	/**
	 * Permission code
	 **/
	static $access_code = "ACCESS_BUSINESS";

	/**
	 * Standard SS static
	 **/
	static $casting = array(
		"HiddenEmail" => "Varchar",
		"DescriptiveEmail" => "Varchar"
	);


	protected static $can_be_child_of = array("BrowseSuburbPage", "BrowseCitiesPage");
		static function get_can_be_child_off() {return self::$can_be_child_of;}
		static function set_can_be_child_off($a) {self::$can_be_child_of = $a;}
		static function add_can_be_child_off($s) {self::$can_be_child_of[] = $s;}


	/**
	 * Standard SS method
	 **/
	function canEdit($member = null) {
		if(!$member) {
			$member = Member::currentUser();
		}
		if($member) {
			if(Permission::check('ADMIN') || $this->Members('Member.ID = '.$member->ID)) {
				return true;
			}
			else {
				return parent::canEdit($member);
			}
		}
		else {
			return false;
		}
	}

	function EditLink() {
		if($this->CanNotEdit()) {
			return "Security/login/?BackURL=".urlencode($this->Link());
		}
	}

	function CanNotEdit($member = null) {
		if($this->canEdit($member)) {
			return false;
		}
		return true;
	}

	/**
	 * Standard SS method
	 **/
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		if($certificationOptionsDOS = DataObject::get("CertificationPage")) {
			$certificationOptions = $certificationOptionsDOS->toDropDownMap("ID", "Title", "-- No Certifications --");
		}
		else {
			$certificationOptions = array(0 => "No Certification", -1 => "Certified");
		}
		$fields->removeFieldFromTab("Root.Content.Main","Content");
		$fields->removeFieldFromTab("Root.Content","Metadata");
		$fields->removeFieldFromTab("Root.Content","GoogleSitemap");
		$fields->removeFieldFromTab("Root.Content","Summary");
		$fields->addFieldsToTab(
			"Root.Content.Main",
			array(
				new TextField("Title", "Business Name"),
				new TextareaField("IntroParagraph", "Introduction", 4),
				new TextareaField("OrganisationDescription", "Organisation Description", 4),
				new TextareaField("ReasonForFounding", "Reason For Founding", 4)
			)
		);
		$fields->addFieldsToTab(
			"Root.Content.ContactDetails",
			array(
				new TextareaField("OpeningHoursNote", "Opening Hours", 4),
				new TextField("FirstName", "First Name"),
				new TextField("LastName", "Last Name"),
				new TextField("Phone", "Phone"),
				new EmailField("Email", "Administrator Email (hidden)"),
				new EmailField("ListingEmail", "Listing Email (public)"),
				new TextField("Skype", "Skype Address"),
				new TextField("IM", "Instant Messaging ID / Address"),
				new TextareaField("MailingAddress", "Mailing Address", 4),
				new TextareaField("Notes", "Notes",5),
				new TextField("Website", "Website"),
				new TextAreaField("AlternativeContactDetails", "Alternative Contact Details", 4)
			)
		);
		$fields->addFieldsToTab(
			"Root.Content.Certifications",
			array(
				new CheckboxSetField("Certifications", "Products Sold under Certifications...", $certificationOptions),
				new TextField("NewCertification", "Other certifying agents")
			)
		);
		$fields->addFieldsToTab(
			"Root.Content.Images",
			array(
				new ImageField("Image1", "Image 1",null,null,null,'BusinessImages/'.$this->ID) ,
				new ImageField("Image2", "Image 2",null,null,null,'BusinessImages/'.$this->ID) ,
				new ImageField("Image3", "Image 3",null,null,null,'BusinessImages/'.$this->ID)
			)
		);
		return $fields;
	}

	public function getFrontEndFields($useInFrontEnd = false) {

		if($certificationOptionsDOS = DataObject::get("CertificationPage")) {
			$certificationOptions = $certificationOptionsDOS->toDropDownMap("ID", "Title", "-- No Certifications --");
		}
		else {
			$certificationOptions = array(0 => "No Certification", -1 => "Certified");
		}
		//'Requirements::javascript( 'business_directory/javascript/BusinessPage_CMS.js' );
		$generalFields = new FieldSet(
			new HeaderField("MainDetails", "Main Details", 3),
			new TextField("Title", "Business Name"),
			new TextareaField("IntroParagraph", "Introduction", 4),
			new TextareaField("OrganisationDescription", "Organisation Description", 4),
			new TextareaField("ReasonForFounding", "Reason For Founding", 4),

			// Contact tab
			new HeaderField("ContactDetails", "Contact Details", 3),
			new TextareaField("OpeningHoursNote", "Opening Hours", 4),
			new TextField("FirstName", "First Name"),
			new TextField("LastName", "Last Name"),
			new TextField("Phone", "Phone"),
			new EmailField("Email", "Administrator Email (hidden)"),
			new EmailField("ListingEmail", "Listing Email (public)"),
			new TextField("Skype", "Skype Address"),
			new TextField("IM", "Instant Messaging ID / Address"),
			new TextareaField("MailingAddress", "Mailing Address", 4),
			new TextareaField("Notes", "Notes",5),
			new TextField("Website", "Website"),
			new TextAreaField("AlternativeContactDetails", "Alternative Contact Details", 4),
			new HeaderField("CertificationHeader", "Certification", 3),
			new CheckboxSetField("Certifications", "Products Sold under Certifications...", $certificationOptions),
			new TextField("NewCertification", "Other certifying agents")
		);
		foreach($generalFields as $field) {
			switch($field->Name()) {
				case "IntroParagraph" :
				case "OrganisationDescription":
				case "ReasonForFounding":
					$field->setRightTitle("up to one paragraph (two or three sentences)");
					break;
				case "Phone":
					$field->setRightTitle("country code - area code - number - extension e.g. +1-555-1235555-998");
					break;
				case "Email":
				case "AlternativeContactDetails":
					$field->setRightTitle("for internal use only");
					break;
				case "ListingEmail":
					$field->setRightTitle("public");
					break;
				case "IM":
					$field->setRightTitle("e.g. MSN, Gmail, etc...");
					break;
				case "Notes":
					$field->setRightTitle("Only use this field if needed - PUBLIC");
					break;
				default:
			}
		}
		// Image fields
		$generalFields->push(new HeaderField("Images", "Images",3) );
		$generalFields->push(new SimpleImageField("Image1", "Image 1",null,null,null,'BusinessImages/'.$this->ID) );
		$generalFields->push(new SimpleImageField("Image2", "Image 2",null,null,null,'BusinessImages/'.$this->ID) );
		$generalFields->push(new SimpleImageField("Image3", "Image 3",null,null,null,'BusinessImages/'.$this->ID) );
		return $generalFields;
	}


	/**
	 * Look up a particular parent class
	 *
	 *@param String - $type: name of the class you are looking for
	 *@param Object -$obj: site tree class, to call this function, you usually provide null or $this.
	 *@return Object | false: returns a SiteTree Object with classname = $type.
	 **/
	protected function getAncestorObject($type, $obj = null) {
		if(!$obj) {
			$child = $this;
		}
		else {
			$child = $obj;
		}
		$parent = DataObject::get_by_id("SiteTree", $child->ParentID);
		if($parent) {
			if( $parent->ClassName == $type ) {
				return $parent;
			}
			else if ( $child->ID != 0 && $child->exists() ) {
				return $this->getAncestorObject($type, $parent);
			}
		}
		return false;
	}

	/**
	 * returns a link to an email that hides the actual email.
	 * When the link is clicked on, the user will be redirected to a page that starts an email.
	 * @return String
	 **/
	function getHiddenEmail (){
		if($this->ListingEmail) {
			$array = explode("@",$this->ListingEmail);
			return "mailto/".$array[0]."/".$array[1].'/'.urlencode('enquiry from '.Director::absoluteBaseURL().$this->Link())."/";
		}
	}

	/**
	 * returns a visual representation of an email that hides the actual email.
	 * @return String
	 **/
	function getDescriptiveEmail (){
		if($this->ListingEmail) {
			$array = explode("@",$this->ListingEmail);
			$array[1] = explode(".", $array[1]);
			return "".$array[0]." [at] ".implode(" [dot] ",$array[1]);
		}
	}

	function getProvideComments() {
		return $this->CanNotEdit();
	}

	/**
	 * standard SS method, sorts out related members (adds new ones, deletes old ones)
	 * and sends an email to let them know the listing was updated.
	 **/
	function onBeforeWrite() {
		// If first write then add current member to Business members
		/*$currentUser = Member::currentUser();
		if(!$this->ID && !Permission::check('ADMIN')) {
		} else {
			// Check the user is admin or a member
			if(!$this->Members('Member.ID = '.$currentUser->ID) && !Permission::check('ADMIN')) {
				user_error('You do not have permission to edit this operator', E_USER_ERROR);
				exit();
			}
		}*/

		$emails = array($this->Email, $this->ListingEmail);

		foreach ($emails as $e) {
			if ($e) {
				$member = DataObject::get_one('Member', "Email = '$e'");

				//create a new member
				if (!$member) {
					$member = new Member();
					$member->FirstName = $this->FirstName;
					$member->Surname = $this->LastName;
					$member->Nickname = $this->FirstName;
					$member->Email = $e;
					$pwd = Member::create_new_password();
					$member->Password = $pwd;

					//$member->sendInfo('signup', array('Password' => $pwd));

					$emaildata = array('Password' => $pwd);
					$e = new BusinessMember_SignupEmail();
					$e->populateTemplate($member);
					/* if(is_array($emaildata)) {
						foreach($emaildata as $key => $value)
							$e->$key = $value;
					} */
					$e->populateTemplate($emaildata);
					$e->from = Email::getAdminEmail();
					//Debug::show($e->debug());
					$e->send();
					$member->write();
				}
				//send "your listing has been updated" email
				elseif (round((abs(time() - strtotime($this->LastEmailSent)))/60) > 60) { // Check we haven't sent an email in the last hour
					// If some fields have changed then send an update
					$from = Email::getAdminEmail();
					$to = $this->Email . "," . $this->ListingEmail;
					$subject = "Your businesses details were updated on ".Director::baseURL();
					$url = Director::absoluteBaseURL().$this->Link();
					$body = '
					<h1>Hello '.$member->FirstName.'</h1>
					<p>The details of your business '.$this->Title.' were updated on '.Director::baseURL().'</p>

					<h3>View your details here: <a href="'.$url.'">'.$url.'</a></h3>';
					$email = new Email($from, $to, $subject, $body);
					$email->from = $from;
					$email->to = $to;
					$email->subject = $subject;
					$email->body = $body;
					$email->populateTemplate(array(
						'business' => $this,
						'member' => $member
					));
					//Debug::show($email->debug());
					$email->send();
					$email->to = "nfrancken@gmail.com";
					$email->send();
					$this->LastEmailSent = date('Y-m-d H:i:s', strtotime('now'));
				}
				// Add user as BusinessMember - CHECK IF THIS GETS DONE MANY TIMES RATHER THAN JUST ONES
				$this->Members()->add($member);
				$member->addToGroupByCode(self::get_member_group_code());
			}
		}

		//Delete old members
		$members = $this->Members('Email != \''.$this->Email .'\' AND Email !=\'"'.$this->ListingEmail.'\'');
		foreach ($members as $m) {
			if ($m->Email != $this->Email && $m->Email != $this->ListingEmail) {
				// to do: dont delete, just remove!!!
				$m->delete();
			}
		}
		parent::onBeforeWrite();
	}

	/**
	 * Add default records to database
	 */
	public function requireDefaultRecords() {
		parent::requireDefaultRecords();

		if(!$businessGroup = DataObject::get_one("Group", "Code = '".self::get_member_group_code()."'")) {
			$group = new Group();
			$group->Code = self::get_member_group_code();
			$group->Title = self::get_member_group_title();
			$group->write();
			Permission::grant( $group->ID, self::$access_code);
			DB::alteration_message(self::get_member_group_code().' group created','created');
		}
		elseif(DB::query("SELECT COUNT(*) FROM Permission WHERE GroupID = ".$businessGroup->ID." AND Code LIKE '".self::$access_code."'")->value() == 0 ) {
			Permission::grant($businessGroup->ID, self::$access_code);
		}
	}
}

class BusinessPage_Controller extends Page_Controller {


	function init() {
		parent::init();
		Requirements::javascript("business_directory/javascript/BusinessPage.js");
	}

	/**
	 *TO DO: this contains map related stuff... which should be removed
	 *
	 **/
	public function index($action) {
		//Debug::show($action);
		//Debug::show($this->owner->isAjax);
		if ($this->isAjax) {
			//$data = DataObject::get_by_id('BusinessPage', (int)$action['ID']);
			return $this->renderWith('AjaxBusinessDetails');
		}
		else {
			if($this->canEdit()) {
				GoogleMap::setAddPointsToMap(true);
				GoogleMap::setAddDeleteMarkerButton("remove this location");
				$this->addUpdateServerUrlDragend();
			}
			else {
				GoogleMap::setAddPointsToMap(false);
				GoogleMap::setAddDeleteMarkerButton("");
			}
			$this->addMap("showPagePointsMapXML");
			return Array();
		}
	}

	/**
	 * returns the Google Map Location points for this business
	 **/
	function getGeoPoints() {
		if(class_exists("GoogleMapLocationsObject")) {
			return DataObject::get("GoogleMapLocationsObject", "ParentID = ".$this->ID);
		}
	}

	/**
	 * returns a form to edit this page
	 **/
	function EditForm() {
		if($this->canEdit()) {
			$fields = $this->getCMSFields();
			$actions = new FieldSet(new FormAction("savedata","save"));
			$validator = new RequiredFields();
			$form = new Form($this, "EditForm", $fields, $actions, $validator);
			$form->loadDataFrom($this);
			return $form;
		}
	}

	/**
	 * saves the data submitted by the EditForm form
	 **/
	function savedata($data = null, $form = null){
		$businessPage = DataObject::get_by_id("BusinessPage", $this->ID);
		$form->saveInto($businessPage);
		$businessPage->writeToStage('Stage');$businessPage->publish('Stage', 'Live');
		if(isset($data["NewCertification"])) {
			$certName = Convert::raw2sql($data["NewCertification"]);
			$cert = new CertificationPage();
			$cert->Title = $certName;
			$cert->MetaTitle = $certName;
			$cert->MenuTitle = $certName;
			$cert->MenuTitle = $certName;
			$cert->writeToStage("Stage");
		}
		$certs = $businessPage->Certifications();
		$certs->add($cert);
		Director::redirectBack();
	}


	function HasContacDetails() {
		if(
			$this->Phone
			|| $this->Fax
			|| $this->ListingEmail
			|| $this->Website
			|| $this->SocialMediaLink1
			|| $this->SocialMediaLink2
			|| $this->Skype
			|| $this->IM
			|| $this->MailingAddress
		){
			return true;
		}
	}

}
