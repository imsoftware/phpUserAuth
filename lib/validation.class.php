<?php

require_once('form.class.php');
require('inputfilter.class.php');

class Validate
{		

	public function __construct() {
	}
	
	// Username validation	
	public static function username($u,$field='user') {
		global $form;
		if( !preg_match("/^[A-z0-9]*(?=.{3,24})([_.]?)[A-z0-9]*$/", $u) ) {
			$form->setError($field,' * Invalid Username!');
			$form->setValue($field,$u);
			return false;
		}
		return true;
	}
	
	//Password validation	
	public static function password($p,$field='pass') {
		global $form;
		//if( !preg_match("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/", $p) ) {
		if( 6 > strlen($p) ) {
			$form->setError($field,' * Invalid Password!');
			return false;
		}
		return true;
	}

	//Email validation
	public static function email($e,$field='email') {
		global $form;
		if( !preg_match("/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/",$_POST['email']) ) {
			$form->setError($field,' * Invalid Email!');
			$form->setValue($field,$e);
			return false;
		}
		return true;
	}
}
$inputFilter = new InputFilter();
$validate = new Validate();
?>