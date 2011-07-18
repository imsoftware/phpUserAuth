<?php
require_once('../lib/userauth.class.php');
$user->is('ADMIN');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<title> phpUserAuth / User administration </title>
	
	<!--[if IE]><script type="text/javascript" src="../inc/ie.js"></script><![endif]-->
	<link rel="stylesheet" type="text/css" href="../inc/table.css">
	<script type="text/javascript" src="../inc/table.js"></script>
	<script type="text/javascript" src="../inc/editable.js"></script>
	<script type="text/javascript" src="../inc/json2.js"></script>
	
	<script type="text/javascript">
	var options = {
		name: "userList",
	  	id: "userList",
	  	columns: [
	  		{ "name": "username", "type": "editable", "label":"Username"},
	  		{ "name": "userlevel", "type": "select", "label":"UserLevel", "values":[
																			{"name":"","value":""},
																			{"name":"User","value":"3"},
																			{"name":"Moderator","value":"2"},
																			{"name":"Admin","value":"1"} 
																		]
			},
			{ "name": "email", "type": "cell", "label":"Email" },
			{ "name": "active", "type": "select", "label":"Status", "values": [ 
																		{"name":"","value":""},
																		{"name":"Active","value":"1"},
																		{"name":"Banned","value":"-1"},
																		{"name":"Logout","value":"Logout"}
																	]
			}
	  	],
		editable: true,
		checkbox: true,
		submitURL: "action.php"
	};
	</script>
</head>
<body>
<?php
$string = "{";
$list = $user->getMembersList(0,100);
$cnt = count($list);
for($i=0;$i<$cnt;$i++) {
	// Active status
	if($list[$i]["active"] == 1) {
		$list[$i]["active"] = "Active";
	}
	else if($list[$i]["active"] == 0) {
		$list[$i]["active"] = "Inactive";
	}
	else {
		$list[$i]["active"] = "Banned";
	}
	// User roles
	if($list[$i]["userlevel"] == 3) {
		$list[$i]["userlevel"] = "User";
	}
	else if($list[$i]["userlevel"] == 2) {
		$list[$i]["userlevel"] = "Moderator";
	}
	else if($list[$i]["userlevel"] == 1) {
		$list[$i]["userlevel"] = "Admin";
	}
	
	$string .= '"'.($list[$i]["userid"]).'":';
	$string .= json_encode($list[$i]);
	$string .= ($i == ($cnt-1)) ? '' : ',';
}
$string .= "}";
?>
<script type="text/javascript">
var str = '<?php echo $string; ?>';
TABLE.init(options);
TABLE.draw(str);
</script>
</body>
</html>
