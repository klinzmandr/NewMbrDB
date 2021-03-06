<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
session_start();
$userid = isset($_REQUEST['userid']) ? $_REQUEST['userid'] : '';
?>
<!DOCTYPE html>
<html>
<head>
<title>Membership System Home Page</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="all">
</head>
<body>
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>

<?php
// NOTE: (isset($var) && !empty($var)) will be equal to !empty($var)
// http://php.net/manual/en/types.comparisons.php

//echo "show login fields"; include 'Incls/vardump.inc.php'; 
if (!isset($_REQUEST['userid'])) {                // no user id
  if (!isset($_SESSION['SessionUser'])) {         // and no session id
    // echo "userid empty, no sessionuser<br>"; 
  	include 'Incls/seccheck.inc.php';             // present login fields
  	exit;
  	}
  }

if (!empty($userid)) {
//  echo "check uid/pw"; include 'Incls/vardump.inc.php';  
  $_SESSION['SessionActive'] = date("Y-m-d H:i:s");
	include_once 'Incls/datautils.inc.php';
	$password = $_REQUEST['password'];
	$ok = checkcredentials($userid, $password);
	if ($ok) {
		//echo "check of user id and password passed<br>";
		addlogentry("Logged In");
		}
	else {
	  unset($_SESSION['SessionActive']);
//			addlogentry("Failed login attempt with password: $password");
//			echo '<h3 style="color: red; ">Failed login attempt</h3>';
		}
	}

echo "<div class=\"container\">";
if (!empty($_SESSION['SessionUser'])) {
  // echo "show logged in"; include 'Incls/vardump.inc.php';
  include_once 'Incls/datautils.inc.php';
  include 'Incls/seccheck.inc.php';         
  include_once 'Incls/mainmenu.inc.php';
	echo '<h4>Session user logged in: ' . $_SESSION['SessionUser'] . '</h4>
	<h5>Security level: ' . $_SESSION['SecLevel'] . '</h5>
	<form class="form-inline" action="indexsto.php?lo=lo" method="post"  id="xform">
  <h3>Membership System Home Page&nbsp  
  <button  class="btn btn-large btn-primary" name="action" value="logout" type="submit" form="xform" class="btn">Logout</button>';
  if ($_SESSION['SecLevel'] == 'admin') {
   echo "&nbsp;&nbsp;&nbsp;<a class=\"btn btn-danger\" href=\"indexadmin.php\">Admim</a>"; }
  echo '
  </h3></form>
  </table></ul>';
	}
else {
	echo '
	<h3>Membership System Home Page&nbsp  
	<a class="btn btn-large btn-primary" href="index.php">Login</a></h3>
	</h3>';
	}
// echo "show after logged in"; include 'Incls/vardump.inc.php';
?>
<!-- START OF PAGE -->
<p>Welcome to the Membership System (MbrDB).  This page will briefly describe the facilities avaiable for administration of this membership system.  Other informaiton is available by clicking the main menu tabs at the top of this page.</p>
<p><b>The membership database contains all the information regarding the contacts, members, volunteers and donors of the organization.  Information contained in this databse is not to be sold or shared and is for the exclusive use of the organization.</b></p>
<p>Access to all the facilities of the system are provided on the main menu located at the top of each page.  An individual member's information may be obtained by entering all or the start of the members unique Member/Contact IDentifier (MCID) in the filter area on the far right.  Entry of a blank filter value will provide the option to do a more generalized search of name, address, email or address information. </p>
<p>The membership database is organized using a unique Member/Contact IDentifier (MCID).  The MCID is comprised of 3 letters (usually the first 3 letters of the members last name) and 2 digits (usually the first 2 digits of the members street address.)  When adding a new MCID a check is made to determine if it is unique.  If it is not merely add 1 to the last digit or use another 2 digit string to make it unique.  After an MCID has been successfully entered a data entry page is proivde to allow entry of further information regarding the member or contact.  That MCID will be used to assoicate all information pertaining to it.</p>
<p>Administrative functions are provided to authorized users that will allow maintenance of the various functions of the system and its associated database.</p>
<p>Security levels are assigned when a new user is registered.  When a user successfully logs in a timed session is established.  Inactivity for longer than 15 minutes will automatically log the user out and require a new login session to be established.</p>

<div class="well">
<h4>GPL License</h4>
<p>Membership System (MbrDB)  Copyright (C) 2013 by Pragmatic Computing, Morro Bay, CA</p>
<p>This program comes with ABSOLUTELY NO WARRANTY.  This is free software.  It may be redistributed under certain conditions.  See &apos;Reports->About MbrDB&apos; for more information.</p>
</div>
</body>
</html>
