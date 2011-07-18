<?php

require_once('lib/userauth.class.php');
require_once('lib/validation.class.php');

if(isset($_REQUEST['v']))
	$form->setValue('v', $_REQUEST['v']);
if(isset($_REQUEST['e']))
	$form->setValue('e', $_REQUEST['e']);

if( isset($_REQUEST['doActivate']) ) {
	if(!$user->checkExisting('user', $_REQUEST['u'])) {
		$form->setError('u', ' * Username Not Found!');
		$form->setValue('u', $_REQUEST['u']);
	}
	
	if($form->numErrors == 0 ) {
		$ret = $user->verifyAccount($_REQUEST['e'], $_REQUEST['v']);
		if($ret == 0) {
			if($user->activateAccount($_REQUEST['u']))
				echo "Account verified and activated successfully!";
			else
				echo "Could not activate account! Contact the administrator";
		}
		else if($ret == 1) {
			echo "Account already active! Please login";
		}
	}
	
	else {
		$_SESSION['valueArray'] = $_REQUEST;
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
	<title>phpUserAuth / Activate account </title>
	<link rel="stylesheet" type="text/css" href="inc/form.css" />
	<script type="text/javascript" src="inc/validation.js"></script>
</head>
<body>
<div class="inputArea">
	<h3>Verify account</h3>
	<form name="verificationForm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
		<label for="u">Username</label>
		<input type="text" name="u" id="u" maxlength="24" tabindex="1" />
		<span class="formError" id="userError"><?php echo $form->error("u"); ?></span>
		
		<label for="pass">Verification Code</label>
		<input type="text" name="e" id="e" <?php if(isset($_REQUEST['e'])) echo " readonly " ?> value="<?php echo $form->value("e"); ?>" tabindex="2" />
		
		<label for="pass">Activation Code</label>
		<br /><input type="text" name="v"  <?php if(isset($_REQUEST['v'])) echo " readonly " ?> value="<?php echo $form->value("v"); ?>" />
		
		<input type="submit" name="doActivate" onclick="return processForm();" id="doActivate" value="Activate account" />
	</form>
</div>
<script type="text/javascript">
function processForm() {
	
	span = document.getElementsByTagName("span");
	for(i=0;i<span.length;i++)
		span[i].innerHTML = '';
		
	user = document.getElementById('u').value;
	return username(user);
}
</script>
<?php
}
?>
