<?php

require_once('lib/userauth.class.php');
require_once('lib/validation.class.php');

if(isset($_POST['doRegister'])) {

	$_POST = $inputFilter->process($_POST);
	
	if($validate->username($_POST['user'])) {
		if($user->checkExisting('user', $_POST['user'])) {
			$form->setError('user',' * Username already taken!');
			$form->setValue('user', $_POST['user']);
		}
	}
	if( $validate->password($_POST['pass']) && $validate->password($_POST['repass'],'repass')	 ) {
		if($_POST['pass'] !== $_POST['repass']) {
			$form->setError('repass',' * Passwords do not match!');
		}
	}
	
	if($validate->email($_POST['email'])) {
		if($user->checkExisting('email', $_POST['email'])) {
			$form->setError('email',' * Email already in use!');
			$form->setValue('email', $_POST['email']);
		}
	}

	/* Validate any custom fields here!!!! */
	
	// If no validation errors, proceed to add user
	if($form->numErrors == 0) {
		/* Some magic to automatically add all fields that are sent through the form
		   so that you need not struggle with adding custom fields
		   YOU MUST VALIDATE CUSTOM FIELDS */
		$fields = unserialize(TABLE_FIELDS);
		$data = array();
		foreach($fields as $k => $v) {
			$data[$k] = isset($_POST[$k]) ? $_POST[$k] : '';
		}
		// If the password has not been sent hashed by javascript, find the sha1 hash now
		if(!isset($_POST['hashed']) || $_POST['hashed'] != 1)
			$data['pass'] = sha1($data['pass']);
		$user->insertUser($data);
	}

	else {
		$_SESSION['valueArray'] = $_POST;
	    $_SESSION['errorArray'] = $form->getErrorArray();
		$user->redirect($_SERVER['PHP_SELF']);
	}
}
else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"/>
	<title>phpUserAuth / Account Registration </title>
	<link rel="stylesheet" type="text/css" href="inc/form.css" />
	<script type="text/javascript" src="inc/sha1.js"></script>
	<script type="text/javascript" src="inc/validation.js"></script>
</head>
<body>
<div class="inputArea">
	<h3>Enter the following details</h3>
	<form name="signupForm" id="signupForm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
		<label for="username">Username</label>
		<input type="text" name="user" id="user" maxlength="24" value="<? echo $form->value("user"); ?>" />
		<span class="formError" id="userError"><? echo $form->error("user"); ?></span>

		<label for="pass">Password</label>
		<input type="password" name="pass" id="pass" />
		<span class="formError" id="passError"><? echo $form->error("pass"); ?></span>

		<label for="repass">Confirm Password</label>
		<input type="password" name="repass" id="repass" />
		<span class="formError" id="repassError"><? echo $form->error("repass"); ?></span>

		<label for="email">Email</label>
		<input type="text" name="email" id="email" maxlength="100" value="<? echo $form->value("email"); ?>" />
		<span class="formError" id="emailError"><? echo $form->error("email"); ?></span>

		<label for="name">Your real name</label>
		<input type="text" name="name" id="name" maxlength="50" value="" />
		<script type="text/javascript">document.write('<input type="hidden" name="hashed" value="1" />');</script>
		<br /><input type="submit" name="doRegister" onclick="return processForm();" value="Signup" />
	</form>
</div>
<script type="text/javascript">
function processForm() {
	span = document.getElementsByTagName("span");
	for(i=0;i<span.length;i++)
		span[i].innerHTML = '';

	user = document.getElementById('user').value;
	pass = document.getElementById('pass').value;
	repass = document.getElementById('repass').value;
	email = document.getElementById('email').value;
				
	if(username(user) && password(pass,'passError') && password(repass,'repassError') && checkpass(pass, repass) && mail(email) ) {
		hash = hex_sha1(pass);
		document.signupForm.pass.value = hash;
		hash = hex_sha1(pass);
		document.signupForm.repass.value = hash;
		return true;
	}
	return false;
}
</script>
</body>
</html>
<?php
}
?>