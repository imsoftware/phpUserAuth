<?php

require_once('lib/userauth.class.php');
require_once('lib/validation.class.php');

if(isset($_POST['doLogin'])) {
	// Some cleaning
	$_POST = $inputFilter->process($_POST);

	$validate->username($_POST['user']);
	$validate->password($_POST['pass']);
	
	if($form->numErrors == 0) {
		// If the password has not been sent hashed by javascript, find the sha1 hash now
		if( !isset($_POST['hashed']) || $_POST['hashed'] != 1 )
			$_POST['pass'] = sha1($_POST['pass']);
		if($user->login($_POST['user'], $_POST['pass'], isset($_POST['remember']) ? true: false)) {
			if( empty($_POST['to']) )
				$to = trim(LOGIN_REDIRECT,'/ ');
			else {
				$to = preg_replace('/\.\.\/|\.\/|[\?]|<|>|=|:/','',$_POST['to']);
			}
			$user->redirect($user->getActualPath(true).$to);
		}
	}
	else {
		$_SESSION['valueArray'] = $_POST;
	    $_SESSION['errorArray'] = $form->getErrorArray();
		$user->redirect($_SERVER['PHP_SELF']);
	}
}

else if(isset($_SESSION[SESSION_VARIABLE])) {
	echo "Welcome ".$user->getProperty('user');
	echo "<br /> You are already logged in. Click <a href='".$user->actualPath."account.php?do=logout'>here to logout</a>";
}

else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"/>
	<title>phpUserAuth / Login </title>
	<link rel="stylesheet" type="text/css" href="inc/form.css" />
	<script type="text/javascript" src="inc/sha1.js"></script>
	<script type="text/javascript" src="inc/validation.js"></script>	
</head>
<body>
<div class="inputArea">
	<h3>Please Login</h3>
	<form name="loginForm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
		<label for="username">Username</label>
		<input type="text" name="user" id="user" maxlength="24" value="<?php echo $form->value("user"); ?>" tabindex="1" />
		<span class="formError" id="userError"><?php echo $form->error("user"); ?></span>

		<label for="pass">Password</label>
		<input type="password" name="pass" id="pass" value="" tabindex="2" />
		<span class="formError" id="passError"><?php echo $form->error("pass"); ?></span>
		
		<br /><input type="hidden" name="to" value="<?php echo isset($_GET['to']) ? $_GET['to'] : ''; ?>" />
		<input type="submit" name="doLogin" id="doLogin" onclick = 'return processForm()' value="Login" />

		<?php if(REMEMBER_USER) { ?>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="remember" id="remember" value="1" tabindex="3" /> Remember Me?
		<?php }?>

		<br /><br /><a href="<?php echo $user->actualPath; ?>forgot.php?do=pass">Forgot Password?</a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="<?php echo $user->actualPath; ?>forgot.php?do=user">Forgot Username?</a>
		<br /><br /><a href="<?php echo $user->actualPath; ?>signup.php">Get an account</a>
		<script type="text/javascript">				
		document.write('<input type="hidden" name="hashed" value="1" />');
		</script>
</form>
</div>
<script type="text/javascript">
function processForm() {
	span = document.getElementsByTagName("span");
	for(i=0;i<span.length;i++)
		span[i].innerHTML = '';
		
	pass = document.getElementById('pass').value;
	user = document.getElementById('user').value;
	
	if(username(user) && password(pass,'passError')) {
		hash = hex_sha1(document.getElementById('pass').value);
		document.loginForm.pass.value = hash;
		return true;
	}
	return false;
}
</script>
<?php
}
?>
