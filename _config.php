<?php


Member::add_extension('Member', 'BusinessMember');

DataObject::add_extension('SiteTree', 'BrowseBusinessDecorator');
Object::add_extension('ContentController', 'BrowseBusinessDecorator_Controller');

//=============================== START business_directory =====================================
//BrowseBusinessDecorator::get_classes_that_can_have_businesses_as_children(array("BusinessListingPage"));
//=============================== END business_directory =====================================
