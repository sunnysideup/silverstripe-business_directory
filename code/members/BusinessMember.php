<?php

/**
 * BusinessMemberRole
 *
 * This decorator adds the needed fields and methods to the {@link Member}
 * object.
 */
class BusinessMember extends DataObjectDecorator {

  /**
	 * Define extra database fields
	 *
	 * Return an map where the keys are db, has_one, etc, and the values are
	 * additional fields/relations to be defined
	 */
	function extraStatics() {
		return array(
			'belongs_many_many' => array (
			  'BusinessPages' => 'BusinessPage'
			)
		);
	}

}

/**
 * Class used as template to send an email to new members
 * @package sapphire
 * @subpackage security
 */
class BusinessMember_SignupEmail extends Email {
	protected $from = '';  // setting a blank from address uses the site's default administrator email
	protected $to = '$Email';
	protected $subject = '';
	protected $body = '';

	function __construct() {
		$this->subject = _t('Member.EMAILSIGNUPSUBJECT', "Your business has been created at ".Director::baseURL());
		$this->body = '
			<h2>' . _t('Member.GREETING','Welcome to '.Director::baseURL()) . ', $FirstName.</h2>
			<p>' . _t('Member.EMAILSIGNUPINTRO1','Your business has been created at '.Director::baseURL().'. Your login details are listed below for future reference.') . '</p>

			<p>' . _t('Member.EMAILSIGNUPINTRO2','You can login to the website using the credentials listed below')  . ':
				<ul>
					<li><strong>' . _t('Member.EMAIL') . ':</strong> $Email</li>
					<li><strong>' . _t('Member.PASSWORD') . ':</strong> $Password</li>
				</ul>
			</p>

			<h3>' . _t('Member.CONTACTINFO','Contact Information') . '</h3>
			<ul>
				<li><strong>' . _t('Member.NAME','Name')  . ':</strong> $FirstName $Surname</li>
				<% if Phone %>
					<li><strong>' . _t('Member.PHONE','Phone') . ':</strong> $Phone</li>
				<% end_if %>

				<% if Mobile %>
					<li><strong>' . _t('Member.MOBILE','Mobile') . ':</strong> $Mobile</li>
				<% end_if %>

			</ul>
		';
	}

	function MemberData() {
		return $this->template_data->listOfFields(
			"FirstName", "Surname", "Email",
			"Phone", "Mobile", "Street",
			"Suburb", "City", "Postcode"
		);
	}
}
