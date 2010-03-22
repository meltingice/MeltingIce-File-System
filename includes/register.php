<?php
/*
*	register.php
*	Adds new user to database
*	Code copyright Ryan "MeltingIce" LeFevre
*/

session_start();

if(isset($_SESSION['username'])){ header('Location: ../index.php'); exit; }
if(!isset($_POST['username'])){ header('Location: ../index.php'); exit; }

/* Recaptcha Validation 
require_once('recaptchalib.php');
$privatekey = "6Lfl9AAAAAAAAOL5RxQWP9FKh2hsIMsSi6oQ0qCQ";
$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
	header('Location: ../index.php#register'); exit;
}*/

include_once('dbconnect.php');
include_once('security.php');
include_once('mail.php');

$username = secureContent($_POST['username']);
$password = hash('sha256',secureContent($_POST['password']));
$email = secureContent($_POST['email']);
$emailagain = secureContent($_POST['emailagain']);
$datejoined = time();
$ipaddress = $_SERVER['REMOTE_ADDR'];

/* PHP-side form validation */
if($email!=$emailagain){ header('Location: ../index.php?register=email#register'); exit; }
if(strpos($email,'@')==false){ header('Location: ../index.php?register=email#register'); exit; }
if(strpos($username,'&')||strpos($username,'#')){ header('Location: ../index.php?register=username#register'); }

$query = "INSERT INTO users (username, password, email, datejoined, ipaddress) VALUES ('$username','$password','$email','$datejoined','$ipaddress')";
$result = mysql_query($query);

if($result)
{
	/* Retrieve the user's ID */
	$IDquery = "SELECT userID FROM users WHERE username='$username' LIMIT 1";
	$IDresult = mysql_query($IDquery);
	$userID = reset(mysql_fetch_row($IDresult));
	
	/* Create the users directory */
	$dummypath = realpath('../user/index.php');
	$boom = explode('index.php',$dummypath);
	$userdir = $boom[0].$username;
	
	/* Create directory for user */
	@mkdir($userdir);
	
	/* Create thumbnail directory */
	@mkdir($userdir."/thumbs");
	
	/* Make an index.php file in users directory to prevent directory listing */
	$file = $userdir.'/index.php';
	$fh = fopen($file, 'a') or die("can't open file");
	fwrite($fh,'<?php header("Location: ../../home.php?user='.$username.'"); ?>');
	fclose($fh);
	
	/* Retrieve the default filespace quota, then add the user to the quota table */
	$query2 = "SELECT defaultQuota FROM adminoptions WHERE optionID='1' LIMIT 1";
	$result2 = mysql_query($query2);
	$quota = reset(mysql_fetch_row($result2));
	
	$query3 = "INSERT INTO quotas (userID,spaceUsed,spaceAvail) VALUES ('$userID','0','$quota')";
	$result3 = mysql_query($query3);
	
	/* Send welcome email to user */
	$title = "Welcome to MeltingIce File System";
	$content = "<h4>Welcome to MeltingIce File System on ".processDomain($_SERVER['HTTP_HOST'])."</h4>";
	$content .= "<p>Your username is '$username' and your password is '".$_POST['password']."'</p>";
	$content .= "<p>Thanks for registering with us!</p>";
	sendMail($title,$content,$username,$email,'MFS2 Registration',"MFS2Registration@".processDomain($_SERVER['HTTP_HOST']));
	
	mysql_close();
	
	/* All finished, go back to index.php */
	header('Location: ../index.php?register=true');
}
else
{
	header('Location: ../index.php?register=false');
}
?>