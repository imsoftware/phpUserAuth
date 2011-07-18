<?php

///////////////////////////////////////////////////////////////////////////////

/* General site settings */
define("SITE_NAME", "phpUserAuth");  // Name of the site

// Path to the site with slash at the end  !!!!!!Important!!!!!!!!!!
/* Example: http://www.example.com/application/ */
define("SITE_PATH","http://example.com/");

define("ADMIN_NAME", "Administrator"); // Name of the administrator
define("ADMIN_EMAIL", "admin@example.com"); // Email of the administrator

/* The following are relative to the SITE_PATH */

/* If you scripts are in the http://example.com/application/user folder, type user as the USER_DIR */
define("USER_DIR", "user");
/* If you want the user to go http://www.example.com/application/login_redirect.php */
define("LOGIN_REDIRECT", "user/account.php");
define("LOGOUT_REDIRECT", "index.php"); // Redirect the user to this page after logout

///////////////////////////////////////////////////////////////////////////////

/* Database Settings */

define("DB_HOST","host"); // Hostname
define("DB_NAME","database"); // Database
define("DB_USER","user"); // Username
define("DB_PASS","password"); // Password
define("TBL_USERS", "userauth"); // Table name

///////////////////////////////////////////////////////////////////////////////

/* User levels. Add more levels here */
define("GUEST", 0);
define("ADMIN", 1);
define("MOD", 2);
define("USER", 3);

///////////////////////////////////////////////////////////////////////////////

/* MAIL SETTINGS */
define("USE_SMTP", FALSE);
define("SMTP_HOST", "");
define("SMTP_PORT", "");
define("SMTP_USER", "");
define("SMTP_PASS", "");
define("USE_SSL", FALSE);

///////////////////////////////////////////////////////////////////////////////

/* Session settings */
define("MULTIPLE_SESSIONS", TRUE); // Can the user have multiple sessions active?
define("SESSION_VARIABLE", "UserAuth"); // Session variable where user info is stored (array)
define("SESSION_TIMEOUT", 60*3); // User timeout in seconds. 0 to disable timeout
define("SESSION_FIELDS",""); // Add table fields that you would like to set in the session variable when a user is loaded (separated by commas)

///////////////////////////////////////////////////////////////////////////////

/* Cookie settings */
define("REMEMBER_USER", TRUE); // Track user using cookie?
define("COOKIE_NAME", "phpUserAuth"); // Cookie Name
define("COOKIE_EXPIRES", 60*60*24);  // Cookie expiry time in seconds (default 1 day)

///////////////////////////////////////////////////////////////////////////////

/* User activation. If both are false, then admin has to activate account manually */
define("SEND_ACTIVATION_MAIL", TRUE); // Send activation email?
define("AUTO_ACTIVATE", FALSE); // Automatically activate user after registration?

///////////////////////////////////////////////////////////////////////////////

/* Set the error reporting level */
define("DEV_MODE", FALSE);

///////////////////////////////////////////////////////////////////////////////

/* Some housekeeping */
header( "Expires: Mon, 20 Dec 1998 01:00:00 GMT" );
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
//ini_set("date.timezone","Asia/Calcutta");

///////////////////////////////////////////////////////////////////////////////


/* Map table fields here. Don't change the key values. Add new fields ONLY TO THE END of the array */
define("TABLE_FIELDS", serialize(array(
	"id" => "userid",
	"user" => "username",
	"pass" => "password",
	"email" => "email",
	"level" => "userlevel",
	"vercode" => "activationHash",
	"active" => "active",
	"session" => "sessionid",
	"time" => "lastActive",
	"name" => "name"
)));

///////////////////////////////////////////////////////////////////////////////