function password(pass, field) {
	if(pass.length < 6) {
		document.getElementById(field).innerHTML = "* Password should be at least 6 characters";
		return false;
	}
	document.getElementById(field).innerHTML = '';
	return true;
}

function username(user) {
	if(user.length < 3) {
		document.getElementById('userError').innerHTML = "Username should be at least 3 characters";
		return false;
	}
	if(!/^[A-z0-9]*(?=.{3,24})([_.]?)[A-z0-9]*$/.test(user) ) {
		document.getElementById('userError').innerHTML = "Invalid username!";
		return false;
	}
	document.getElementById('userError').innerHTML = '';
	return true;
}

function mail(email) {
	if(email.length < 5){
		document.getElementById('emailError').innerHTML = "Invalid email address!";
		return false;
	}
	if(!/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/.test(email)) {
		document.getElementById('emailError').innerHTML = "Invalid email address!";
		return false;
	}
	document.getElementById('emailError').innerHTML = '';
	return true;
}

function checkpass(pass, repass) {
	if(pass !== repass) {
		document.getElementById('repassError').innerHTML = "Passwords do not match!"
		return false;
	}
	return true;
}