<?php
require_once('lib/userauth.class.php');

if( ( isset($_GET['do']) && isset($_GET['e']) && isset($_GET['v']) ) || ( isset($_SESSION[SESSION_VARIABLE]) && !isset($_POST['doReset']) ) ) {
	if( !isset($_SESSION[SESSION_VARIABLE]) ) {
		if( 2 != $user->verifyAccount($_GET['e'], $_GET['v'] ) ) {
			echo "Password reset not requested or reset code invalid!";
			exit;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"/>
	<title>phpUserAuth / Change Password </title>
	<link rel="stylesheet" type="text/css" href="inc/form.css">
	<script type="text/javascript" src="inc/sha1.js"></script>
	<script type="text/javascript" src="inc/validation.js"></script>
</head>
<body>
	<form name='resetPasswordForm' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' autocomplete="off">
		<?php if(isset($_SESSION[SESSION_VARIABLE])) {?>
		<div class="inputArea">
			<h3>Enter your current password</h3>
			<label for="currentpass">Current Password</label>
			<input type="password" name="currentpass" id="currentpass" tabindex="1" />
			<span class="formError" id="currentpassError"></span>
		</div>
		<div class="inputArea">
			<label for="email">New email address</label>
			<input type="text" name="email" id="email" tabindex="1" />
			<span class="formError" id="emailError"></span>
		</div>
		<?php
		}
		?>
		<div class="inputArea">
			<h3>Change Password</h3>
			
			<label for="pass">Password</label>
			<input type="password" name="pass" id="pass" tabindex="2" />
			<span class="formError" id="passError"></span>
			
			<label for="repass">Confirm Password</label>
			<input type="password" name="repass" id="repass" tabindex="3" />
			<span class="formError" id="repassError"></span>
	
			<?php
			if(isset($_GET['e']))
				echo "<input type='hidden' name='e' value='".$_GET['e']."' />";
			?>
			<script type="text/javascript">				
			document.write('<input type="hidden" name="hashed" value="1" />');
			</script>

			<br /><input type="submit" name="doReset" onclick="return processForm();" value="Update" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="#" onclick="history.go(-1);">Cancel</a>
		</div>
	</form>
	<script type="text/javascript">
	function processForm() {

		span = document.resetPasswordForm.getElementsByTagName("span");
		for(i=0;i<span.length;i++)
			span[i].innerHTML = '';
		
		pass = document.getElementById('pass').value;
 		repass = document.getElementById('repass').value;
		
		<?php
		if(isset($_SESSION[SESSION_VARIABLE])) {
		?>
		currentpass = document.getElementById('currentpass').value;
		email = document.getElementById('email').value;

		if(email == '' && pass == '') {
			return false;
		}
		else {
			if(!password(currentpass, 'currentpassError')) {
				return false;
			}
			if(email != '') {
				if(!mail(email))
					return false;
			}
			if(pass != '') {
				if( password(pass, 'passError') && password(repass, 'repassError') && checkpass(pass, repass) ) {
					document.getElementById('pass').value = hex_sha1(pass);
					document.getElementById('repass').value = hex_sha1(repass);
				}
				else {
					return false;
				}
			}
			document.getElementById('currentpass').value = hex_sha1(currentpass);
			return true;
		}
		
		<?php
		}
		else {
		?>
			if( password(pass, 'passError') && password(repass,'repassError') && checkpass(pass, repass) ) {
				document.getElementById('pass').value = hex_sha1(pass);
				document.getElementById('repass').value = hex_sha1(repass);
				return true;
			}
			return false;
		<?php
		}
		?>
		return true;
	}
	</script>
</body>
</html>
<?php
exit;
}
else if( isset($_POST['doReset']) ) {
	// Resetting password through email
	if(isset($_POST['e'])) {
		if($_POST['pass'] !== $_POST['repass']) {
			echo "Passwords do not match. Error changing password!";
			exit;
		}
		// If the password has not been sent hashed by javascript, find the sha1 hash now
		if(!isset($_POST['hashed']) || $_POST['hashed'] != 1) {
			$_POST['pass'] = sha1($_POST['pass']);
		}
		if($user->changePassword($_POST['pass'], $_POST['e'])) {
			echo "Password changed successfully! Please login with your new password";
			exit;
		}
		else {
			die("Error changing password!");
		}
	}
	// Changing password after login
	else if( isset($_SESSION[SESSION_VARIABLE]) && isset($_POST['currentpass']) ) {
		$user->is();
		// If the password has not been sent hashed by javascript, find the sha1 hash now
		if(!isset($_POST['hashed']) || $_POST['hashed'] != 1)
			$_POST['currentpass'] = sha1($_POST['currentpass']);
				
		// Check if the current password entered is valid
		$email = $user->checkExisting('pass', $_POST['currentpass']);
		
		// If the user is valid
		if($email) {
			// If the email needs to be updated
			if(!empty($_POST['email'])) {
				if( $email == $_POST['email'] ) {
					echo "New email id is the same as old. Nothing to update";
				}
				if ( !$user->checkExisting('email', $_POST['email']) ) {
					if( $user->updateProperty('email', $_POST['email'], $_SESSION[SESSION_VARIABLE]["id"]) ) {
						echo "Email updated successfully!";
					}
					else {
						echo "Nothing to update";
					}
				}
				else {
					echo "Email address already exists! Not updating";
				}
			}
			
			// If the pass needs to be updated
			if( !empty($_POST['pass']) && !empty($_POST['repass']) ) {
				if($_POST['pass'] !== $_POST['repass']) {
					echo "Passwords do not match. Error changing password!";
					exit;
				}
				
				if(!isset($_POST['hashed']) || $_POST['hashed'] != 1)
						$_POST['pass'] = sha1($_POST['pass']);
		
				if($user->changePassword( $_POST['pass'], $email) ) {
					$user->logout("Password changed successfully! You have been logged out for security reasons",true);
				}
				else {
					die("Error changing password!");
				}
			}
		}
	}
}
?>
