<?php

require "config.php";

error_reporting(E_ERROR | E_PARSE);

$ds = ldap_connect($ldaphost, $ldapport) or die("Could not connect to LDAP server!");

ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

if ($ds){
	$uid = $_POST['username'];
	if(!ctype_alnum($uid)){
		die("Invalid username!");
	}
	$username = 'uid='.$uid.",ou=users,".$ldapbase;
	$password = $_POST['password'];
	$group = $_POST['group'];
	if (!in_array($group, $allowed, $strict=true)){
		die("Selected group is not allowed!");
	}
	$ldapbind = ldap_bind($ds, $username, $password);
	if ($ldapbind){
		$filter = "(&(objectClass=posixGroup)(memberUid=$uid))";
		$result = ldap_search($ds, 'ou=groups,'.$ldapbase, $filter);

		$info = ldap_get_entries($ds, $result);
		$found = false;
		foreach ($info as $g){
			if (!isset($g["cn"]))
				continue;
			$cn = $g["cn"][0];
			if ($cn == $group){
				$found = true;
			}
		}
		if (!$found){
			die("No access to group!");
		}
		$dir = $dirs[$group];
		unlink("/etc/openvpn/$dir/clients/$uid.ovpn");
		unlink("/etc/openvpn/$dir/pki/issued/$uid.crt");
		shell_exec("./gen_cert \"$dir\" \"$uid\"");
		$file = "/etc/openvpn/$dir/clients/$uid.ovpn";
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
	}else{
		echo "Incorrect password!";
	}
}

?>

