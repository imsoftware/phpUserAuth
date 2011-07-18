<?php
require_once('../lib/userauth.class.php');
$user->is('ADMIN');

$field = $_GET['id'];
$field = explode('_',$field);
$value = $_GET['value'];

if($value == 'Logout') {
	$array = array("sessionid" => "", "lastActive" => "");
	if($user->updatePropertyArray($array, $field[0])) {
		echo "Success";
	}
	else {
		echo "Error";
	}
}
else {
	if($user->updateProperty($field[1], $value, $field[0])) {
		echo "Success";
	}
	else {
		echo "Error";
	}
}
?>