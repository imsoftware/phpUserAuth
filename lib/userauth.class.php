<?php
/**
 * UserAuth class for authentication of users. Needs config.php
 *
 * @author Srinath
 * @version 0.1
 * @copyright http://iambot.net, July 18, 2010
 **/

require_once("config.php");

/**
 * Main class for user authentication
 *
 * @package default
 * @author Srinath
 **/
class UserAuth
{
	private $db; // Our MySQLi DB object
	private $userID; // The userid of the active user
	private $table; // Array containing table fields
	private $session = array("session","id","time"); // Array containing fields that have to be stored in session variable
	private $userData = array(); // Array of user data
	public $actualPath;
	
	/**
	 * Constructor - Sets up some global settings, connects to db
	 *
	 * @param MySQLi database object (optional)
	 **/
	public function __construct($dbc=null) {
		// If running on dev mode
		if(DEV_MODE) {
			error_reporting(E_ALL | E_NOTICE | E_STRICT);
			ini_set("display_errors", TRUE);
		}
		// Get the actual working path of the user scripts
		$this->actualPath = $this->getActualPath();
		// Set the parameters
		$this->table = unserialize(TABLE_FIELDS);
		if(SESSION_FIELDS != '') {
			$a = explode(',',SESSION_FIELDS);
			foreach($a as $k)
				array_push( $this->session, $k );
		}
		// If no database object is passed, create a new db connection
		if( !is_object($dbc) ) {
			$this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			if ( mysqli_connect_errno() ) {
			  return $this->error("DB Connection Error: ". mysqli_connect_error());
			}
		}
		// else assign the connection object to the passed connection
		else {
			$this->db = $dbc;
		}
		// If the session is not started yet
		if( !isset($_SESSION) ) {
			session_start();
		}
		// If there is a cookie, retrieve its value and try logging in
	    if ( REMEMBER_USER && isset($_COOKIE[COOKIE_NAME]) ) {
			$this->loadCookie();
	    }
	}

	/**
	 * Loads the cookie and logs in the user
	 *
	 * @param none
	 * @return bool
	 **/
	private function loadCookie() {
		$u = unserialize(base64_decode($_COOKIE[COOKIE_NAME]));
		// Check if a user exists in the table with the username and session id 
		$sql = "SELECT `{$this->table['id']}`, `{$this->table['active']}` FROM ".TBL_USERS." 
				WHERE `{$this->table['user']}` = '".$u['uname']."' 
				AND `{$this->table['session']}` = '".$u['pass']."' LIMIT 1";
		$result = $this->db->query($sql);
		if($result->num_rows == 1) {
			$row = $result->fetch_assoc();
			// If the user is active, he can be logged in
			if($row[$this->table['active']] == 1) {
				$this->userID = $row[$this->table['id']];
				$this->postLogin(true);
			}
			else {
				return $this->error("Account not verified or inactive");
			}
		}
		else {
			$this->logout("Session Invalid",true);
		}
		return true;
	}

	/**
	 * Destructor. Closes database connection
	 *
	 * @param none
	 * @return void
	 **/
	/*
	public function __destruct() {
		if($this->db) {
			$thread = $this->db->thread_id;
			$this->db->kill($thread);
			$this->db->close();
		}
	}
	*/
	
	/**
  	 * Login function - Called when logging in through form
	 *
  	 * @param string username
  	 * @param string password
 	 * @param boolean Set cookie?
  	 * @return bool
     **/
	public function login( $uname, $pass, $remember=false ) {
    	$uname    = $this->escape($uname);
    	$password = $pass;
 		$pass = $this->escape($pass);
		
		$result = $this->db->query("SELECT `{$this->table['id']}`,`{$this->table['pass']}`,`{$this->table['active']}` 
					FROM ".TBL_USERS." 
					WHERE `{$this->table['user']}` = '$uname' 
					OR `{$this->table['email']}` = '$uname' LIMIT 1");
		// If user not found
		if ($result->num_rows == 0) {
			return $this->error("User not found!");
		}
		// If user is found
		else {
			$row = $result->fetch_array();
			// Compare passwords
			if(!$this->comparePassword($pass, $row[$this->table['pass']])) {
				return $this->error("Invalid username/Password");
			}
			// If passwords match but user is not verified
			if($row[$this->table['active']] < 1) {
				return $this->error("Account not verified or inactive");
			}
			// If everything goes well, set the userID
			$this->userID = $row[$this->table['id']];
		}
		$this->postLogin();
		// If a cookie has to be set, do it
		if ( REMEMBER_USER && $remember) {
			$cookie = base64_encode(serialize(array('uname'=>$uname,'pass'=>$this->userData[$this->table['session']])));
		  	$a = setcookie(COOKIE_NAME, $cookie, time()+COOKIE_EXPIRES, '/');
		}
		return true;
	}	

	/**
  	 * Sets session variables after logging in either through form or cookie
	 *
  	 * @param boolean Using cookie?
  	 * @return bool
     **/
	private function postLogin($cookie=false) {
		$this->loadUser($this->userID);
		if(!$cookie) {
			$this->userData[$this->table['session']] = MULTIPLE_SESSIONS ? $this->userData[$this->table['session']] : md5(uniqid(rand(), TRUE));
		}
		$sql = "UPDATE ".TBL_USERS." 
				SET `{$this->table['session']}` = '".$this->userData[$this->table['session']]."' WHERE `{$this->table['id']}` = '$this->userID'";
		$res = $this->db->query($sql);
		if(!$res) {
			return $this->error($this->db->error);
		}
		
		$_SESSION[SESSION_VARIABLE] = array();
		foreach ($this->session as $field) {
			if(isset($this->userData[$this->table[$field]]))
				$_SESSION[SESSION_VARIABLE][$field] = $this->userData[$this->table[$field]];
		}
		if( SESSION_TIMEOUT != 0)
			$_SESSION[SESSION_VARIABLE]['time'] = time();
		return true;
	}
  
	/**
	 * User callable function. Checks if the user is logged in as the given user level
	 *
	 * @param Userlevel(s) defined in config.php separated by comma
	 * @return bool
	 **/
	public function is($level="USER,MOD,ADMIN") {
		// Are the user details loaded into the session variable?
		if( $this->isLoaded() ) {
			// If a session timout is defined, check it here
			if( SESSION_TIMEOUT != 0) {
				if(time() - $_SESSION[SESSION_VARIABLE]['time'] > SESSION_TIMEOUT) {
					$this->logout("Session Timed out", true);
				}
				$_SESSION[SESSION_VARIABLE]['time'] = time();			
			}
			// Check if the user exists in the database
			$sql = "SELECT `{$this->table['level']}` FROM ".TBL_USERS." WHERE `{$this->table['id']}` = '".$_SESSION[SESSION_VARIABLE]['id']."' 
					AND `{$this->table['session']}` = '".$_SESSION[SESSION_VARIABLE]['session']."'";
			$res = $this->db->query($sql);
			if($res->num_rows != 1) {
				$this->logout("Session Invalid", true);
			}
			$row = $res->fetch_assoc();
			$userLevel = $row[$this->table['level']];
			// If the user is all right, update the last active time in the db
			$sql = "UPDATE ".TBL_USERS." SET `{$this->table['time']}` = '".date('Y-m-d H:i:s', time()-1)."' 
					WHERE `{$this->table['id']}` = '".$_SESSION[SESSION_VARIABLE]['id']."' 
					AND `{$this->table['session']}` = '".$_SESSION[SESSION_VARIABLE]['session']."'";
			$res = $this->db->query($sql);
			// Check for the userlevels
			$level = explode(',',$level);
			$levels = array();
			foreach($level as $k) {
				array_push($levels, constant($k));
			}
			if( in_array ($userLevel, $levels) ) {
				return true;
			}
			else {
				$this->error("Insufficient Privilege");
				exit;
			}
		}
		// Here, the user isn't logged in. So redirect him automatically to the login page
		// Some basic regex cleanup so that the user isn't redirected to a page outside the site
		$url = parse_url($this->getActualPath(true));
		$replace = '/'.preg_replace('/\//','\/', $url['path']).'/';
		$to = preg_replace($replace, '', $_SERVER['PHP_SELF']);
		$path = $this->actualPath."login.php?to=$to";
		$this->redirect($path);
	}

	/**
	 * Load the user's data from the table
	 *
  	 * @access private
  	 * @param string $userID
  	 * @return bool
     **/
	private function loadUser( $userID ) {
		$result = $this->db->query("SELECT * FROM ".TBL_USERS." WHERE `{$this->table['id']}` = '".$this->escape($userID)."' LIMIT 1");
    	if ( $result->num_rows == 0 )
    		return false;
		$this->userData = $result->fetch_assoc();
    	return true;
  	}

	/**
  	 * Produces the result of addslashes() with more safety
	 *
  	 * @access private
  	 * @param string $str
  	 * @return string
     **/  
  	private function escape($str) {
    	$str = get_magic_quotes_gpc()?stripslashes($str):$str;
    	$str = $this->db->real_escape_string($str);
    	return $str;
  	}

	/**
     * Error holder for the class
	 *
     * @access private
     * @param string $error
     * @return bool
     **/  
  	private function error($error) {
   		echo '<b>Error: </b>'.$error.'<br />';
   		return false;
  	}
	
	/**
	 * Is the user logged in?
	 *
	 * @access public
	 * @return bool
	 **/
	public function isLoaded() {
		return isset($_SESSION[SESSION_VARIABLE]) ? true : false;
	}
	
	/**
	 * Produces a random 8 bit alpha numeric number for salting the password
	 *
	 * @access private
	 * @return string
	 **/
	private function getPasswordSalt() {
	    return substr( str_pad( dechex( mt_rand() ), 8, '0', STR_PAD_LEFT ), -8 );
	}

	/**
	 * Creates a salted password for storing in the database
	 *
	 * @access private
	 * @return string
	 **/
	private function getPasswordHash( $salt, $password ) {
	    return $salt . ( hash( 'sha1', $salt . $password ) );
	}

	/**
	 * Checks if the password provided, and the hashed password from the db match
	 *
	 * @access private
	 * @return bool
	 **/
	private function comparePassword( $password, $hash ) {
	    $salt = substr( $hash, 0, 8 );
	    return $hash === $this->getPasswordHash( $salt, $password );
	}
	
	/**
  	 * Logs out the user by clearing the session and deleting the cookie
	 *
	 * @access public
  	 * @param string Reason for logging out
	 * @param bool Should the script exit after logging out? 
     **/
  	public function logout($reason = 'User Logged out', $die = false) {
		// Do this only if the user is logged in
		if($this->isLoaded()) {
			$res = $this->db->query("UPDATE ".TBL_USERS." SET `{$this->table['time']}` = '' 
					WHERE `{$this->table['id']}` = ".$_SESSION[SESSION_VARIABLE]['id']."");
			// Delete the cookie
			setcookie(COOKIE_NAME, '', time()-36000, '/');
			unset($_SESSION[SESSION_VARIABLE]);
   			$this->userData = null;
			if($die) {
	  			echo "$reason <br /> Please click <a href='".$this->actualPath."login.php'>here</a> to login again<br />";
				exit;
			}
			else {
				echo "$reason. <br /> Redirecting to main page in 3 seconds";
				echo '<br /> Click <a href="'.$this->getActualPath(true).trim(LOGOUT_REDIRECT,'/ ').'">here</a> if your browser does not redirect you';
				?>
				<script type="text/javascript">
				function redirectTo() {
					window.location = <?php echo "'".$this->getActualPath(true).trim(LOGOUT_REDIRECT,'/ ')."'"; ?>;
				}
				window.onload = function() { setTimeout(redirectTo, 3000); }
				</script>
			<?php
				exit;
			}
		}
		//If the user tries logging out without logging in, redirect to the main page of the application
		else {
			$this->redirect($this->getActualPath(true));
		}
  	}
  
	/**
  	 * Get a property of a user. You should give here the name of the field that you seek from the user table
	 *
	 * @access public
  	 * @param string $property
  	 * @return string
     **/
  	
	public function getProperty($property) {
		// You cannot get certain sensitive fields
		$ignore = array('pass','vercode');
		if(in_array($property, $ignore)) {
			return false;
		}
		
		if(!empty($this->userData)){
			return $this->userData[$this->table[$property]];
		}
		
		$this->loadUser($_SESSION[SESSION_VARIABLE]['id']);
		return $this->userData[$this->table[$property]];
  	}
  
	public function getMembersList($start=0, $count=10) {
		$list = Array();
		$i = 0;
		$result = $this->db->query("SELECT `{$this->table['id']}`,`{$this->table['user']}`,`{$this->table['email']}`,`{$this->table['level']}`,`{$this->table['active']}`
					FROM ".TBL_USERS." WHERE `{$this->table['id']}` != '1' ORDER BY `{$this->table['id']}` LIMIT $start, $count");
		while($row = $result->fetch_assoc()) {
			$list[$i] = $row;
			$i++;
		}
		return $list;
	}
  
	/**
  	 * Is the user an active user?
  	 * @return bool
     **/
  	public function isActive() {
    	return $this->userData[$this->table['active']];
  	}
	
	/**
  	 * Is the user an active user?
  	 * @return bool
     **/
	public function redirect($to) {
		if(!headers_sent()) {
			header('Location: '.$to);
		}
		else {
			?>
			<script type="text/javascript">
			<!--
			window.location = <?php echo "'".$to."'"; ?>;
			//-->
			</script>
			<?php
			echo "If you are seeing this, we were not able to redirect you automatically";
			echo "<br /> Please click <a href='$to'>here</a> to goto the intended page";
		}
	}
	/*
     * Creates a user account. The array should have the form 'database field' => 'value'
     * @param array $data
     * return int
     **/
	public function insertUser($data) {
    	if (!is_array($data)) {
			$this->error('Data to be inserted is not an array');
		}
		
		// CRITICAL!! DO NOT CHANGE!!! GENERATES A SALTED SHA1 HASH OF THE PASSWORD TO STORE IN THE DB
		$data['pass'] = $this->getPasswordHash( $this->getPasswordSalt(), $data['pass'] );
		
		// Generate a random verification code
		$data['vercode'] = $this->randomPassword(50);
		$data['level'] = USER;
		$data['active'] = AUTO_ACTIVATE ? 1 : 0;
    	foreach ($data as $k => $v )
			$data[$k] = "'".$this->escape($v)."'";
		$sql = "INSERT INTO ".TBL_USERS." (`".implode('`, `', array_values($this->table))."`) VALUES (".implode(", ", $data).")";
		$result = $this->db->query($sql);
		// If the user is inserted successfully
		if( $this->db->affected_rows == 1 ) {
			// If the user should be auto activated, activate him and ask him to login
			if(AUTO_ACTIVATE) {
				echo "Your account has been created and activated! Click <a href='login.php'>here</a> to login";
				exit;
			}
			// If an activation mail has to be sent, do that
			else if(SEND_ACTIVATION_MAIL) {
				$data['email'] = trim($data['email'],"'");
				$data['vercode'] = trim($data['vercode'],"'");
				if($this->sendVerificationMail($data['email'], $data['vercode'])) {
					echo "Verification mail sent to ".$data['email']." <br />Please check your mailbox for instructions on how to verify your account";
				}
				else {
					echo "Error sending verification mail. Please try again later";
				}
				exit;
			}
			// If neither, the admin has to approve the account manually
			else {
				echo "Your account has been created. However, the administrator has to approve it manually before it can be used";
				exit;
			}
		}
		else {
			echo "Error inserting user into database. Please contact the site administrator";
			exit;
		}
  	}
  
	/*
     * Creates a random password. You can use it to create a password or a hash for user activation
     * param int $length
     * param string $chrs
     * return string
     **/
  	
	private function randomPassword($length=10, $chrs = '1234567890AbCdEfGhIjKlMnOpQrStuVwXyZ') {
    	$pwd ='';
		for($i = 0; $i < $length; $i++) {
        	$pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
    	}
    	return $pwd;
  	}

  	/**
  	 * Activates the user account
  	 * @return bool
     **/
  	function activateAccount($user) {
   		$sql = "UPDATE ".TBL_USERS." SET `{$this->table['active']}` = '1'
				WHERE `{$this->table['user']}` = '{$this->escape($user)}'";
		$res = $this->db->query($sql);
		return $this->db->affected_rows;
	}

	function checkExisting($field, $value) {
		if($field !== 'pass') {
			$res = $this->db->query("SELECT COUNT(*) FROM ".TBL_USERS." 
					WHERE `{$this->table[$field]}` = '{$this->escape($value)}' LIMIT 1");
			$row = $res->fetch_array();
			return $row[0];
		}
		else if($field == 'pass') {
			$res = $this->db->query("SELECT `{$this->table['pass']}`, sha1({$this->table['email']}) 
					FROM ".TBL_USERS." WHERE `{$this->table['id']}` = '".$_SESSION[SESSION_VARIABLE]['id']."' LIMIT 1");
			if($res->num_rows != 1)
				return false;
			$row = $res->fetch_array();
			if(!$this->comparePassword($value, $row[0])) {
				return $this->error("Your current password is wrong! Please try again");
			}
			else {
				return $row[1];
			}
		}
	}


	/**
	 * Checks for verification credentials
	 * 
	 * @return array	an array containing a status code and status message
	 **/

	public function verifyAccount( $email, $vercode ) {
		$query = "SELECT `{$this->table['active']}` FROM ".TBL_USERS."
					WHERE `{$this->table['vercode']}` = '{$this->escape($vercode)}'
					AND sha1({$this->table['email']}) = '{$this->escape($email)}' LIMIT 1";
		$res = $this->db->query($query);
		if($res->num_rows == 0) {
			echo "That didn't seem right! You probably broke the activation/verification code";
			echo "<br / Contact the site administrator for help";
			exit;
		}
		$row = $res->fetch_assoc();
		return $row[$this->table['active']];
	}
	
	public function changePassword($p, $e) {
		// CRITICAL!! DO NOT CHANGE!!! GENERATES A SALTED SHA1 HASH OF THE PASSWORD TO STORE IN THE DB
		$p = $this->getPasswordHash( $this->getPasswordSalt(), $p );
		
		$res = $this->db->query("UPDATE ".TBL_USERS." SET `{$this->table['pass']}` = '{$this->escape($p)}',
					`{$this->table['active']}` = 1, `{$this->table['session']}` = ''
					WHERE sha1({$this->table['email']}) = '{$this->escape($e)}'");
		return $this->db->affected_rows;
	}
	
	public function updateProperty($field, $value, $id) {
		if(!$this->isLoaded())
			return false;
		$res = $this->db->query("UPDATE ".TBL_USERS." SET `$field` = '{$this->escape($value)}' 
				WHERE `{$this->table['id']}` = '{$this->escape($id)}'");
		return $this->db->affected_rows;
	}
	
	public function updatePropertyArray($array, $id ) {
		if(!$this->isLoaded())
			return false;
		$sql = "UPDATE ".TBL_USERS." SET ";
		foreach($array as $k => $v) {
			$sql .= "`$k` = '{$this->escape($v)}',";
		}
		$sql = rtrim($sql, ",");
		$sql .= " WHERE `{$this->table['id']}` = '{$this->escape($id)}'";
		$res = $this->db->query($sql);
		return $this->db->affected_rows;
	}

	/**
	 * Sends an email to a user with a link to verify their new account
	 * 
	 * @param string $email	The user's email address
	 * @param string $ver	The random verification code for the user
	 * @return boolean		TRUE on successful send and FALSE on failure
	 */
	private function sendVerificationMail($to, $vercode) {
		$e = sha1($to); // For verification purposes
		$subject = "Activation email from " . SITE_NAME;
		$details = array
		(
			'{VCODE}'		=> $vercode,
			'{ECODE}' => $e,
			'{SITE_NAME}'	=> SITE_NAME,
			'{SITE_ADDRESS}' => $this->actualPath,
			'{ADMIN_NAME}' => ADMIN_NAME,
			'{ADMIN_EMAIL}'=> ADMIN_EMAIL
		);
		$path = $this->getActualDirectory();
		$message = $this->parseTemplate( $details, $path."/templates/verification.html");
		if ( $this->sendEmail ( $subject, $to, $message ) ) {
			return true;
		}
		return false;
	}
	
	public function resendVerificationMail($email) {
		if($this->checkExisting('email', $email)) {
			$res = $this->db->query("SELECT `{$this->table['vercode']}`, `{$this->table['active']}` FROM ".TBL_USERS." 
					WHERE `{$this->table['email']}` = '{$this->escape($email)}'");
			$row = $res->fetch_array();
			if($row[1] == 1) {
				echo "User is already active!";
				exit;
			}
			if($this->sendVerificationMail($email, $row[0]))
				return true;
		}
		return false;
	}
	
	public function sendUsername($email) {
		if($this->checkExisting('email',$email)) {
			$res = $this->db->query("SELECT `{$this->table['user']}` FROM ".TBL_USERS."
					WHERE `{$this->table['email']}` = '{$this->escape($email)}' LIMIT 1");
			$row = $res->fetch_assoc();
			
			// Send the mail
			$subject = "Your username for ".SITE_NAME;
			$details = array
			(
				'{SITE_NAME}' => SITE_NAME,
				'{SITE_ADDRESS}' => $this->getActualPath(true),
				'{ADMIN_NAME}' => ADMIN_NAME,
				'{ADMIN_EMAIL}' => ADMIN_EMAIL,
				'{USERNAME}' => $row[$this->table['user']]
			);
			$path = $this->getActualDirectory();
			$message = $this->parseTemplate($details, $path.'/templates/username.html');
			if( $this->sendEmail($subject, $email, $message) )
				return true;
			return false;
		}
		else {
			echo "Email address not found!";
			exit;
		}
	}
	
	public function sendPasswordReset($email) {
		if($this->checkExisting('email', $email)) {
			$v = $this->randomPassword(50);
			$res = $this->db->query("UPDATE ".TBL_USERS." 
					SET`{$this->table['vercode']}` = '{$v}', `{$this->table['active']}`= 2
					WHERE `{$this->table['email']}` = '{$this->escape($email)}'");
			if($this->db->affected_rows != 1) {
				echo "Error updating verification code! Please try again later";
				exit;
			}
			
			// Send the email
			$e = sha1($email); // For verification purposes
			$subject = "Reset your password for " . SITE_NAME;
			$details = array
			(
				'{VCODE}'		=> $v,
				'{ECODE}' => $e,
				'{SITE_NAME}'	=> SITE_NAME,
				'{SITE_ADDRESS}' => $this->actualPath,
				'{ADMIN_NAME}' => ADMIN_NAME,
				'{ADMIN_EMAIL}'=> ADMIN_EMAIL
			);
			$path = $this->getActualDirectory();
			$message = $this->parseTemplate( $details, $path."/templates/password.html");
			if ( $this->sendEmail ( $subject, $email, $message ) ) {
				return true;
			}
			return false;
		}
		else {
			echo "Email address not found!";
			exit;
		}
	}
	
	private function parseTemplate( $data, $page ) {
		$tags = array();
		$values = array();
		foreach ($data as $k =>  $v) {
			array_push($tags, $k);
			array_push($values, $v);
		}
		$page = file_get_contents($page);
		return str_replace($tags, $values, $page);
	}
	
	public function sendEmail ( $subject, $to, $body, $from = FALSE ) {
		require_once('mailer.class.php');
		$mailer = new PHPMailer();
		//do we use SMTP?
		if ( USE_SMTP ) {
			$mailer->IsSMTP();
			$mailer->SMTPAuth = true;
			$mailer->Host = SMTP_HOST;
			$mailer->Port = SMTP_PORT;
			$mailer->Password = SMTP_PASS;
			$mailer->Username = SMTP_USER;
			if(USE_SSL)
				$mailer->SMTPSecure = "ssl";
		}
		
		$mailer->SetFrom($from?$from:ADMIN_EMAIL, ADMIN_NAME);
		$mailer->AddReplyTo ( ADMIN_EMAIL, ADMIN_NAME );

		$mailer->AddAddress($to);
		$mailer->Subject = $subject;
		//$mailer->WordWrap = 100;
		$mailer->IsHTML ( TRUE );
		$mailer->MsgHTML($body);
		
		require_once('util.class.php');
		$mailer->AltBody  =  Util::html2text ( $body );
		
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

		if ( ! $mailer->Send() ) {
			return FALSE;
		}
		else {
			$mailer->ClearAllRecipients ();
			$mailer->ClearReplyTos ();
			return TRUE;
		}
	}
	
	public function getActualPath($onlySite = FALSE) {
		if( !defined('SITE_PATH') || SITE_PATH == "" )
			$path = "http://".$_SERVER['SERVER_NAME']."/";
		else
			$path = trim(SITE_PATH,'/ ').'/';
		if($onlySite) 
			return $path;
		
		if( !defined('USER_DIR') || USER_DIR == "" )
			$dir = "";
		else 
			$dir = trim(USER_DIR,'/ ');

		if(empty($dir))
			return $path;
		return $path.$dir.'/';
	}
	
	public function getActualDirectory() {
		$path = pathinfo(__FILE__,PATHINFO_DIRNAME);
		$path .= '/../';
		$path = realpath($path);
		return rtrim($path,'/ ');
	}
		
} // end of class

$user = new UserAuth();
