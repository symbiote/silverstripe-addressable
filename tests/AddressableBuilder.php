<?php

class AddressableBuilder extends SapphireTest{

	protected static $disable_themes = true;
	protected static $use_draft_site = false;

	public function setUp(){
		parent::setUp();

		ini_set('display_errors', 1);
		ini_set("log_errors", 1);
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
	}

	public function logOut(){
		$this->session()->clear('loggedInAs');
		$this->session()->clear('logInWithPermission');
	}

	public function testAddressable(){}

}

class AddressableTestDataObject extends DataObject{

	private static $extensions = array(
		'Addressable',
		'Geocodable'
	);

}
