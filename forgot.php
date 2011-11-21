<?php
require_once('lib/userauth.class.php');
require_once('lib/validation.class.php');

if(isset($_POST['doSend'])) {
	
	/* Recover username */
	if($_POST['doSend'] == 'Send Username') {
		if($user->sendUsername($_POST['email'])) {
			echo "Your username has been sent to ".$_POST['email'];
			exit;
		}
		else {
			echo "There was an error sending the mail. Please try again later";
			exit;
		}
	}
	/* Password reset */
	else if($_POST['doSend'] == 'Reset Password') {
		if($user->sendPasswordReset($_POST['email'])) {
			echo "A mail has been sent to ".$_POST['email']." with instructions on how to reset your password";
			exit;
		}
		else {
			echo "There was an error sending the password reset mail. Please try again later";
			exit;
		}
	}
	/* Resend activation mail */
	else if($_POST['doSend'] == 'Resend Activation') {
		if($user->resendVerificationMail($_POST['email'])) {
			echo "Activation mail has been sent to ".$_POST['email']." with instructions on how to activate your account";
			exit;
		}
		else {
			echo" There was an error sending the activation mail. Please try again later";
			exit;
		}
	}
}

if( isset($_GET['do']) && $_GET['do'] == 'user') {
	$title = 'Recover Username';
	$button = 'Send Username';
}
else if( isset($_GET['do']) && $_GET['do'] == 'activation') {
	$title = 'Resend Activation Mail';
	$button = 'Resend Activation';
}
else {
	$title = 'Reset Password';
	$button = 'Reset Password';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>phpUserAuth / <?php echo $title;?> </title>
	<link rel="stylesheet" type="text/css" href="inc/form.css" />
	<script type="text/javascript" src="inc/validation.js"></script>
</head>
<body>
<div class="inputArea">
	<h3><?php echo $title;?></h3>
	<form name="forgotForm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
		<label for="email">Enter your email address</label>
		<input type="text" name="email" id="email" value="<?php echo $form->value("email"); ?>" tabindex="2" />
		<span class="formError" id="emailError"><?php echo $form->error("email"); ?></span>
	
		<input type="submit" name="doSend" id="doSend" onclick="return processForm();" value="<?php echo $button;?>" />

		<br /><br /><a href="<?php echo $user->actualPath; ?>forgot.php?do=user">Forgot Username?</a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="<?php echo $user->actualPath; ?>forgot.php?do=activation">Resend Activation Mail</a>
	</form>
<script type="text/javascript">
function processForm() {
	
	span = document.getElementsByTagName("span");
	for(i=0;i<span.length;i++)
		span[i].innerHTML = '';
		
	email = document.getElementById('email').value;
	if(mail(email))
		return true;
	return false;
}		
</script>
</div>
</body>
</html>
