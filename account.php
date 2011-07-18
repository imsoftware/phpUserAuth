<?php
require_once('lib/userauth.class.php');

// Logout
if(isset($_GET['do']) && $_GET['do'] == 'logout') {
	$user->logout('You logged out successfully!');
}
$user->is();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<title> phpUserAuth / User Account </title>
</head>
<body>
<?php
echo "Hi ".$user->getProperty('name'). ". Thank you for logging in";
echo "<br /> Your email id is: ".$user->getProperty('email');
echo "<br /> You can log out by clicking <a href='account.php?do=logout'>here</a>";
?>
</body>
</html>