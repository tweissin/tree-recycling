<?php

function logToFile($type, $msg)
{ 
// open file, based on type 

   $fd = fopen( "log.txt", "a");

   if( $type == 'n')
       $action = "New Request: ";
   else if( $type == 'f')
       $action = "Followup to: ";
   else if( $type == 'a')
       $action = "Add Pickup: ";
   else if( $type == 'e')
       $action = "Email confirm: ";
   else
       $action = "Other: ";

   // prepend date/time to message

   $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $action . $msg; 
   fwrite($fd, $str . "\n");
   fclose($fd);
}

//
// only support POST - if someone accesses the file to look at it, they'll get nothing
//

// print_r( $_POST);

ob_start();  //begin buffering the output 

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $type = $_GET[ 'type'];
    if( $type == 'n')
    {
	// new pickup request - format it and add to the temporary file

	$name = $_POST['name'];
	$addr = $_POST['address'];
	$email = $_POST['email'];
	$email_confirm = $_POST['email_confirm'];
	$phone = $_POST['phone'];
	$date = $_POST['date'];
	$small = $_POST['small'];
	$large = $_POST['large'];
	$source = $_POST['source'];
	$comments = $_POST['comments'];

	// replace "\n" and "\r" with " " in the comments

	$lng = $_POST['addr_lng'];
	$lat = $_POST['addr_lat'];

	$msg = $lat . "|" . $lng . "|" . $date . "|" . $name . "|" . $addr . "|" . $email . "|" . $email_confirm . "|" . $phone . "|" . $small . "|" . $large . "|" . $source . "|" . $comments;

	logToFile( 'n', $msg);

        require( 'config.php');

	$connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

	$selected = mysql_select_db( DB_NAME, $connid);
	if ( $selected == false)
	{
	     print '<p> Database not selected</p>';
	     print mysql_error();
	     return;
	}

	$stmt = "INSERT INTO tmp_pickup (temp, route, lat, lng, weekend, name, street, email, confirm, phone, small, large, source, comments) values ('y','','" .
	    $lat . "','" .
	    $lng . "','" .
	    $date . "','" .
	  mysql_real_escape_string( $name) . "','" .
	    $addr . "','" .
	    $email . "','" .
	    $email_confirm . "','" .
	    $phone . "','" .
	    $small . "','" .
	    $large . "','" .
	    $source . "','" .
	  mysql_real_escape_string( $comments) . "')";

	mysql_query( "BEGIN") or die( "BEGIN error: " . mysql_error());

	$data = mysql_query( "$stmt");
	if ( !$data)
	{
	     print '<p> insert error</p>';
	     print mysql_error();
	     return;
	}
	mysql_query( "COMMIT") or die( "COMMIT error: " . mysql_error());

	logToFile( 'n', '--+ success');

	$goto = "location: http://" . $_SERVER['SERVER_NAME'] . "/thank_you.html";
	header( $goto);
    }
    else
    {
	$goto = "location: http://" . $_SERVER[ 'SERVER_NAME'];
	header ( $goto);
    }

    ob_flush();
}

?>
