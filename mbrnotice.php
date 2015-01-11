<!DOCTYPE html>
<html>
<head>
<title>Membership Notice</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body onchange="flagChange()">

<?php
session_start();
//include 'Incls/vardump.inc';
include 'Incls/seccheck.inc';
include 'Incls/mainmenu.inc';
include 'Incls/datautils.inc';

$tname = isset($_REQUEST['template']) ? $_REQUEST['template'] : "";
$mcid = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : $_SESSION['ActiveMCID'];
$_SESSION['ActiveMCID'] = $mcid;

$templatedir = 'templates/letters';

// set up default page if no MCID is given
if ($mcid == "") { 
	$mcinfo = "<div class=container>";
	$mcinfo .= "<h3>Membership Notices</h3>"; 
	$mcinfo .= "<p>This page will assist in the development of printing a label and/or letter notice to mail to the member whose membership has expired. Usually, this page is used after selection of an MCID from the \"Display Expired\" or the \"List In-Progress Reminders\" listing.</p>
	<p>It should be noted that a this notification process can NOT be initiated to send a notice to a member that does not have an expired membership. It should also be noted that an automatic entry to the members correspondence records is done as a part of completing this process that will record that the notice has been sent.</p>";
	$mcinfo .= "<p>The templates listed allow different messages to be accessed before being sent to the member.  Usually these message are sent in relation to an expired membership.  The selected letter template plus other mailing information is placed in the \"labelsandletters\" table with the current date.</p>
<p>Labels and letters are selected by creation date, editted and printed using LibreOffice using the appropriate label and/or letter templates accessed from the \"labelsandletters\" table of the database.</p>
<p>It should be noted that information in the \"labelsandletters\" table of the database and must be printed and sent as a separate action.  This facility will automatically add a note in the members correspondence log that this action has taken place as of this date.</p></div>";
	$mcinfo .= "<script src=\"jquery.js\"></script> <script src=\"js/bootstrap.min.js\"></script></body></html>";
	echo $mcinfo;
	exit;
	}

// first let's make sure that the member is not expired
	$date = calcexpirationdate();																				// exp period: 11 months
	$sql = "SELECT * from `donations` WHERE `MCID` = '$mcid' AND `Purpose` = 'dues' AND `DonationDate` > '$date'";
	$results = doSQLsubmitted($sql);				

// parse out those rows to just show the latest payment made
	$results->data_seek(0);
	$nbr_rows = $results->num_rows;

	if ($nbr_rows > 0) {				//	any row returned means dues payment made within exp. period
		$row = $results->fetch_assoc();
		//echo "<pre>"; print_r($row); echo "</pre>";
		print <<<expNotice
<h3>MCID <a href="mbrinfotabbed.php">$mcid</a> does NOT have an expired membership</h3>
<!-- <a class="btn btn-primary" href="mbrinfotabbed.php" name="filter" value="$mcid">CANCEL AND RETURN</a> -->
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</div>
</body>
</html>
expNotice;
		exit;
		}

// check if MCID is inactive
//echo "This is the active MCID: " . $_SESSION['ActiveMCID'] . "<br>";
$sql = "SELECT * FROM `members` WHERE MCID = '".$mcid."'";
$res = doSQLsubmitted($sql);
$rc = $res->num_rows;
$row = $res->fetch_assoc();
if ($row[Inactive] == 'TRUE') {
	echo "<h3>Member <a href=\"mbrinfotabbed.php\">$mcid</a> marked as inactive</h3>
	<p>Please update the record before proceeding.</p>
	<script src=\"jquery.js\"></script><script src=\"js/bootstrap.min.js\"></script></div></body></html>";
	exit;
	}

// does MCID want to get mail
//echo "<pre>MCID record"; print_r($row); echo "</pre>";
if ($row['Mail'] == 'FALSE') {
print <<<noNotice
<h3>Member <a href="mbrinfotabbed.php">$mcid</a> does not want to receive any correspondence from PWC.</h3><br>
<!-- <a class="btn btn-primary" href="mbrinfotabbed.php" name="filter" value="$mcid">CANCEL AND RETURN</a> -->
<script src="jquery.js"></script><script src="js/bootstrap.min.js"></script></div></body></html>
noNotice;
	exit;
	}

// check if mailing infor is complete
if ((strlen($row['NameLabel1stline']) == 0) OR (strlen($row['AddressLine']) == 0) OR (strlen($row['City']) == 0) OR (strlen($row['State']) == 0) OR (strlen($row['ZipCode']) == 0)) {
	echo "<h3>Mailing informamtion for member <a href=\"mbrinfotabbed.php\">$mcid</a> is incomplete.  Please correct this before proceeding.</h3>.<br />";
	//echo "<pre>dump of mbr info "; print_r($row); echo "</pre>";
	echo '<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</div>
</body>
</html>';
	exit;
	}

// now we can list the template list for selection
if ($tname == "") {
$sql = "SELECT * FROM `templates` WHERE `Type` = 'mail';";
$res = doSQLsubmitted($sql);
print <<<tempForm1
<div class="container"><h3>Membership Mail Notice</h3> 
<h4>Send a mail reminder to: <a href="mbrinfotabbed.php">$mcid</a></h4>
Select an Letter template from the selection list:<br>
<form action="mbrnotice.php" method="post">
<select name="template">
<option value=""></option>
tempForm1;
	while ($t = $res->fetch_assoc()) {
		$name = $t[Name]; $tid = $t[TID];
		echo "<option value=\"$tid\">$name</option>";
		}
print <<<tempForm2
</select>
<input type="submit" name="submit" value="Submit">
</form><br /><br />
<!-- <a class="btn btn-primary" href="mbrinfotabbed.php" name="filter" value="$mcid">CANCEL AND RETURN</a> -->
</div>	
<script src="jquery.js"></script><script src="js/bootstrap.min.js"></script></div></body></html>
tempForm2;
	exit();
	}

// we are good, read the template and prep edit form
// template name given so read it and set up for edit form
//echo "read template and create edit form<br />"
echo "<div class=\"container\"><h3>Edit and Send the Message to ".$_SESSION['ActiveMCID']."</h3>";
$sql = "SELECT * FROM `templates` WHERE `TID` = '$tname';";
$tres = doSQLsubmitted($sql);
$t = $tres->fetch_assoc();
$templatename = stripslashes($t[Name]);
$template = stripslashes($t[Body]);

$org = $row['Organization']; $name = $row['NameLabel1stline']; $addr = $row['AddressLine'];
$city = $row['City']; $state = $row['State']; $zip = $row['ZipCode']; $corrsal = $row['CorrSal'];
print <<<editForm
<script type="text/javascript" src="js/nicEdit.js"></script>
<script type="text/javascript">
bkLib.onDomLoaded(function() {
	new nicEditor({buttonList : ['fontSize', 'fontFormat', 'left', 'center', 'right', 	'bold','italic','underline','indent', 'outdent', 'ul', 'ol', 'hr', 'forecolor', 
	'bgcolor','link','unlink']}).panelInstance('area1');
});
</script>

Template Name: $templatename<br />
<form action="mbrnoticeupd.php" method="get"  class="form">
<textarea id="area1" name="Letter" rows="20" cols="80">$template</textarea><br />
<input type="hidden" name="MCID" value="$mcid">
<input type="hidden" name="Organization" value="$org">
<input type="hidden" name="NameLabel1stline" value="$name">
<input type="hidden" name="AddressLine" value="$addr">
<input type="hidden" name="City" value="$city">
<input type="hidden" name="State" value="$state">
<input type="hidden" name="ZipCode" value="$zip">
<input type="hidden" name="CorrSal" value="$corrsal">
<input type="hidden" name="Notes" value="$templatename">
<input type="submit" name="submit" value="Submit">
<form><br /><br />

editForm;

?>
<script src="jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</div>
</body>
</html>
