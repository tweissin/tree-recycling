<?php 
require( "header.php");


function logToFile($type, $msg)
{ 
// open file, based on type 

   $fd = fopen( "plog.txt", "a");

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



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_GET[ 'type']) && $_GET['type'] == 'a')
{
    $t = "a";  // add pickup to weekend list(s)

//print_r( $_POST);
//print_r( $_GET);
//print_r( $_SERVER);

    logToFile( $t, "Add Pickup");

    $id   = $_POST['unique_id'];
    $name = $_POST['name'];
    $addr = $_POST['addr'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $small = $_POST['under8'];
    $large = $_POST['over8'];
    $comments = $_POST['comments'];
    $list_element = $_POST['list_element'];

    $lng = $_POST['addr_lng'];
    $lat = $_POST['addr_lat'];

    // this layout has to be identical to what set_routes.html and map_routes.html reads

    $pickup = "|" . $lat . "|" . $lng . "|" . $date . "|" . $name . "|" . $addr . "|" . $phone . "|" . $small . "|" . $large . "|" . $comments . "\n";

    if( $_POST['followup'] == "true")
    {
	$f_up = $_POST['followup_email'];

	logToFile( "f", $f_up);

	// some sort of exception, send the email and remove from the list

	$subject = 'Pickup Followup note';
	$headers = 'From: donotreply@troop4hopkinton.org' . "\r\n" . 'Reply-To: webmaster@troop4hopkinton.org' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

	$msg = "The pickup request below has been flagged for followup\n";
	$msg .= "and has been removed from the pickup list\n\n";

	$msg .= "name : " . $_POST['name'] . "\n";
	$msg .= "addr : " . $_POST['addr'] . "\n";
	$msg .= "email: " . $_POST['email_addr'] . "\n";
	$msg .= "phone: " . $_POST['phone'] . "\n";
	$msg .= "date : " . $_POST['date'] . "\n";
	$msg .= "small: " . $_POST['under8'] . "\n";
	$msg .= "large: " . $_POST['over8'] . "\n";
	$msg .= "  lng: " . $_POST['addr_lng'] . "\n";
	$msg .= "  lat: " . $_POST['addr_lat'] . "\n";
	$msg .= "comments" . $_POST['comments'] . "\n";

	mail( $f_up, $subject, $msg, $headers);
    }
    else
    {
	logToFile( $t, "processing " . $list_element . " with data " . $pickup);

	// update the "temp" flag for the pickup

        require( '../config.php');

	$connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

	$selected = mysql_select_db( DB_NAME, $connid);
	if ( $selected == false)
	{
	     print '<p> Database not selected</p>';
	     print mysql_error();
	     return;
	}

	$stmt = "UPDATE tmp_pickup SET temp = 'n' where id=" . $id;

	//print_r( $stmt);

	$result = mysql_query( "$stmt");
	if (!$result)
	{
	    echo 'Could not update pickup: ' . mysql_error();
	    exit;
	}

	logToFile( $t, "Updated: " . $name . " " . $addr . " pickup for " . $date);

	// if requested, send the confirmation

	if( $_POST[ 'email_confirm'] == "yes")
	{
            $email_addr = $_POST['email_addr'];  // email address for confirmation

	    // get the actual dates from the file

	    $date_filename = "../dates.lst";
	    $date_handle = fopen( $date_filename, "r");
	    $str = fgets( $date_handle);

	    list( $date1_str, $date2_str, $date3_str, $season) = split( ";", $str);
	    fclose( $date_handle);


	    $subject = 'Christmas Tree Pickup confirmation';
	    $headers = 'From: tree-request@troop4hopkinton.org' . "\r\n" . 'Reply-To: tree-request@webmaster@troop4hopkinton.org' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

	    if( $date == "date_0")       $date = $date1_str;
	    else if( $date == "date_1")  $date = $date2_str;
	    else if( $date == "date_2")  $date = $date3_str;

        $msg = "Dear " . $name . ": \n";
        $msg .= "This email is to confirm your Christmas Tree Pickup request for " . $date . "\n\n";
        $msg .= "Please have your tree(s) curbside by 8am.\n\n";
        $msg .= "Thank you for your support of Scouting in Hopkinton.\n\n";
        $msg .= "Boy Scout Troop 4\nhttp://www.troop4hopkinton.com\n";

	    mail( $email_addr, $subject, $msg, $headers);

	    logToFile( "e", $email_addr);
	}
    }
}
?>

<STYLE TYPE="text/css">
#display {width:500px; border:10px ridge blue; padding:20px}

.hiddenControl {visibility:hidden}
.displayControl {visibility:visible}
</STYLE>


<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=AIzaSyDCnEZxN2selIUgvRhwtI5DrPLtRrD9HKI" type="text/javascript"></script>

<script type="text/javascript">

var map = null;
var geocoder = null;

// intersection of Rts 85 and 135
var hop_lat = 42.228454;
var hop_lng = -71.522069;

<?php

require_once( '../inc/rcube_shared.inc');

/* retrieve the elements from the temporary file, creating the array containing all
   and set the number of addresses we have
 */
$n_items = build_list();

echo "var nAddresses = " . $n_items . ";";

function build_list()
{
    require( '../config.php');

    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
         print '<p> Database not selected</p>';
         print mysql_error();
         return;
    }

    $data = mysql_query( "select * from tmp_pickup where temp= 'y'");

//lines[0] = ['42.243731','-71.490015','date_3','Joe Glover','6 Hawthorne Lane','joeglover@comcast.net','Yes','617-519-7789','0','1','flyer/poster where I bought the tree','feel free to come the 10th if that works as well.  tree will be waiting.']; 

   $i = 0;
    echo "var lines = [];\n";
    while ($line = mysql_fetch_array($data, MYSQL_ASSOC))
    {
        echo "lines[" . $i . "] = " . array2js( array_values( $line), "string" ) . ";\n";
        $i++;
    }

    mysql_free_result($data);

    return $i;
}

?>


function updateAddressCount()
{

    document.getElementById("nAddresses").innerHTML = "There are <b>" + nAddresses.toString() + "</b> addresses to be processed.";
}

var nTries = 0;


function getList()
{
//    lines = doc.split("=");

    updateAddressCount();

    //alert( "nAddr: " + nAddresses);

    //alert( lines);

    for( var i = 0; i < nAddresses; i++)
    {
        // make sure there's a valid line, then just do the 1st line in the file

	if (lines[i].length > 1)
	{
            view_pickup( i);
            break;
        }
    }
    list_element = i;

    if( nAddresses > 1)
        document.getElementById( "next_button").disabled = false;

}

function initialize()
{
    if (GBrowserIsCompatible()) {

        map = new GMap2(document.getElementById("map_canvas"));

        map.setCenter( new GLatLng( hop_lat, hop_lng), 11);
        map.setMapType(G_NORMAL_MAP);
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        geocoder = new GClientGeocoder();
    }

    // and tell the user how many requests we need to process


    resetWeekendBox();
    resetCompletionForm();

    // get the list

    getList();
}

function resetCompletionForm()
{
    document.completion_buttons.email_addr.value = "";
    document.completion_buttons.email_confirm.value = "no";

    document.completion_buttons.unique_id.value = "";
    document.completion_buttons.name.value = "";
    document.completion_buttons.addr.value = "";
    document.completion_buttons.phone.value = "";
    document.completion_buttons.date.value = "";
    document.completion_buttons.under8.value = "";
    document.completion_buttons.over8.value = "";
    document.completion_buttons.comments.value = "";

    document.completion_buttons.addr_lat.value = 0;
    document.completion_buttons.addr_lng.value = 0;
}


function in_good_order( doc)
{
    document.completion_buttons.followup.value = false;

    document.completion_buttons.unique_id.value = unique_id;
    document.completion_buttons.name.value = name;
    document.completion_buttons.addr.value = addr;
    document.completion_buttons.phone.value = phone;
    document.completion_buttons.date.value = date;
    document.completion_buttons.under8.value = small;
    document.completion_buttons.over8.value = large;
    document.completion_buttons.comments.value = comments;
    document.completion_buttons.list_element.value = list_element;

    if( send_email_confirm == true)
    {
	document.completion_buttons.email_addr.value = email;
	document.completion_buttons.email_confirm.value = "yes";
    }
    else
	document.completion_buttons.email_confirm.value = "no";

    document.completion_buttons.addr_lat.value = lat;
    document.completion_buttons.addr_lng.value = lng;

    hideCompletionButtons();
    resetWeekendBox();   

    return true;
}

function not_in_good_order( doc)
{
    in_good_order( doc);

    document.completion_buttons.followup.value = true;
    document.completion_buttons.followup_email.value = document.getElementById("text_NIGO_mail").value;

    if( document.getElementById("text_NIGO_mail").value == "")
    {
	alert( "Please enter an email to receive followup email");
        displayCompletionButtons();
        return false;
    }
    return true;
}

function display()
{
//  alert( document.completion_buttons.name.value + " " + document.completion_buttons.addr.value + " " + document.completion_buttons.phone.value + " " + document.completion_buttons.list_element.value);

//    alert( "all_done()");

    return true;
}


function displayCompletionButtons()
{
    document.getElementById("button_IGO").className="displayControl";
    document.getElementById("button_NIGO").className="displayControl";
    document.getElementById("text_NIGO_mail").className="displayControl";
//    document.getElementById("instructions").className="displayControl";

    /* email button and address will be displayed if confirm requested */

    document.getElementById("button_confirm").className = "displayControl";
    document.getElementById("email").className = "displayControl";
}

function hideCompletionButtons()
{
    document.getElementById("button_IGO").className="hiddenControl";
    document.getElementById("button_NIGO").className="hiddenControl";
    document.getElementById("text_NIGO_mail").className="hiddenControl";
//   document.getElementById("instructions").className="hiddenControl";

    document.getElementById("button_confirm").className = "hiddenControl";
    document.getElementById("confirm_note").className = "hiddenControl";
    document.getElementById("email").className = "hiddenControl";
    //    document.getElementById("email").value = "";
}

function resetWeekendBox()
{
    document.completion_buttons.pickup[0].checked = false;
    document.completion_buttons.pickup[1].checked = false;
    document.completion_buttons.pickup[2].checked = false;
}


function setWeekendBox( date)
{
    resetWeekendBox();

    if( date == "date_0")
        document.completion_buttons.pickup[0].checked = true;
    else if( date == "date_1")
        document.completion_buttons.pickup[1].checked = true;
    else if( date == "date_2")
        document.completion_buttons.pickup[2].checked = true;
    else
        alert( "invalid value for SetWeekendBox() " + date);

}


var unique_id;
var name;
var addr;
var email;
var phone;
var date;
var small;
var large;
var send_email_confirm;
var comments;

var lat = 42.228454;   // for now, just the center of town
var lng = -71.522069;

var list_element;

function set_email_confirm( doc)
{
    send_email_confirm = true;

    document.getElementById("confirm_note").className = "displayControl";
}



function set_prev_next()
{
//alert( "list: " + list_element + " nAddr: " + nAddresses);

    document.getElementById( "next_button").disabled = (list_element >= (nAddresses-1));
    document.getElementById( "prev_button").disabled = (list_element == 0);

    document.getElementById( "confirm_note").className = "hiddenControl";
}

function next_pickup()
{
    map.removeOverlay(marker);

    view_pickup( ++list_element);
    set_prev_next();
}

function prev_pickup()
{
    map.removeOverlay(marker);

    view_pickup( --list_element);
    set_prev_next();
}


function view_pickup( i)
{
    // === split the line that comes from the db

    parts = lines[i];

    //  $msg = $lat . "|" . $lng . "|" . $date . "|" . $name . "|" . $addr . "|" . $email . "|" $email_confirm . "|" . $phone . "|" . $small . "|" . $large . "|" . $source . "|" . $comments . "=";

    unique_id  = parts[0];  /* the unique id */
    tmp = parts[1];  /* is it temp or approved pickup request */
    route = parts[2]; /* route name - ignore for now */

    lat = parts[3];
    lng = parts[4];
    date = parts[5];
    name = parts[6];
    addr  = parts[7];
    email = parts[8];
    email_confirm = parts[9];
    phone = parts[10];
    small = parts[11];
    large = parts[12];
    source = parts[13];
    comments = parts[14];

    // get the next address from the file, turn on the address buttons

    document.getElementById("disp_name").innerHTML = name;
    document.getElementById("disp_addr").innerHTML = addr;
    document.getElementById("disp_phone").innerHTML = phone;
    document.getElementById("disp_under8").innerHTML = small.toString();
    document.getElementById("disp_over8").innerHTML = large.toString();
    document.getElementById("disp_comments").innerHTML = comments;

    setWeekendBox( date);    
    displayCompletionButtons();
    displayAddr( lat, lng);

    if( email_confirm == "Yes")
    {
	document.getElementById("button_confirm").className = "displayControl";
	document.getElementById("button_confirm").disabled = false;
	document.getElementById("email").className = "displayControl";
	document.getElementById("email").value = email;
	send_email_confirm = true;
    }
    else
    {
	document.getElementById("button_confirm").className = "displayControl";
	document.getElementById("button_confirm").disabled = true;
	document.getElementById("email").value = "";
	send_email_confirm = false;
    }

}


var marker;

function displayAddr( lat, lng)
{
    var point = new GLatLng( lat, lng);

    map.setCenter(point, 16);

    marker = new GMarker(point, {draggable: true});
    map.addOverlay(marker);
}

</script>

<title>T4 Tree Request Processing</title>
</HEAD>
<body onload="initialize()" onunload="GUnload()">

<?php

require( "navbar.php");

navbar(1);
?>

<h3>Review Tree Pickup Requests</h3>

<!-- the location for the count of addresses in the temporary file -->
<br>
<div id="nAddresses"></div><br>

Review the pickup request below.  If the request looks to be in good
order, check the email confirmation button (if it had been requested)
and click the <b>"Add Pickup"</b> button to have the request added to
the weekend pickup list.

<br><br>
If there's an issue with the request, click the <b>"followup"</b> button, add
your email address and an email with the information will be sent to you for
later follow up.

<br>
<br>

<a href="http://troop4hopkinton.org"><button type="button">Finish Later</button></a>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 

<a onClick="next_pickup()"><button type="button" id="next_button" disabled>View Next Pickup</button></a>

&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 

<a onClick="prev_pickup()"><button type="button" id="prev_button" disabled >View Prev Pickup</button></a>


<hr>

<table>
<tr><td>

<div id="map_canvas" style="vertical-align: top; float: left; width: 350px; height: 300px"></div>

</td>
<td>
<div id="address_section" style="width:450px;">

<form name="completion_buttons" action="process_list.php?type=a" onsubmit="display(this)" enctype="application/x-www-form-urlencoded" method="post">

<table width=100%><tbody>
    <tr><td width="10px"></td><td width=25%><b>Name:</b></td><td><div id="disp_name"></div></td></tr>
    <tr><td width="10px"></td><td width=25%><b>Address:</b></td><td><div id="disp_addr"></div></td></tr>
    <tr><td width="10px"></td><td width=25%><b>Phone:</b></td><td><div id="disp_phone"></div></td></tr>

    <tr><td width="10px"></td><td width=25%>&nbsp;</td></tr>
    <tr><td width="10px"></td><td width=25%><b>Pickup:</b></td><td>


	  Week 1: <input name="pickup" type="checkbox">&nbsp; &nbsp; &nbsp;
	  Week 2: <input name="pickup" type="checkbox">&nbsp; &nbsp; &nbsp;

	  Week 3: <input name="pickup" type="checkbox"><br>

    </td></tr>

    <tr><td width="10px"></td><td width=25%><b>Trees Under 8':</b></td><td><div id="disp_under8"></div></td></tr>
    <tr><td width="10px"></td><td width=25%><b>Trees Over 8' :</b></td><td><div id="disp_over8"></div></td></tr>
    <tr><td width="10px"><td valign="top"><b>Comments: </b></td><td width=\"150\"><div id="disp_comments"></div></td></tr>

  <tr>

    <td width="10px"></td><td colspan=2>

        <a onClick="set_email_confirm( this)"><button type="button" id="button_confirm" class="hiddenControl" disabled>Send Email Confirm?</button></a>
  
	   <input name="email" id="email" type="text" class="hiddenControl"><br>

        <div id="confirm_note" class="hiddenControl" style="display:inline">confirmation email will be sent</div><br>

           <input name="email_addr" id="email_addr" type="hidden">
           <input name="email_confirm" id="email_confirm" type="hidden">

	   <input name="unique_id" id="unique_id" type="hidden">

	   <input name="name" id="name" type="hidden">
	   <input name="addr" id="addr" type="hidden">
	   <input name="phone" id="phone" type="hidden">
	   <input name="date" id="date" type="hidden">
	   <input name="under8" id="under8" type="hidden">
	   <input name="over8" id="over8" type="hidden">
	   <input name="comments" id="comments" type="hidden">

	   <input name="followup" id="followup" type="hidden">

	   <input name="followup_email" id="followup_email" type="hidden">

	   <input name="addr_lat" id="addr_lat" type="hidden">
	   <input name="addr_lng" id="addr_lng" type="hidden">

           <input name="list_element" id="list_element" type="hidden">

    </td>
  </tr>


</tbody></table>


<p>Choose to either Add Pickup to list or to send yourself an email for later followup.</p></br>

<div style="margin-left: 30px">
   <input id="button_IGO" TYPE="submit" VALUE="Add pickup" onClick="return in_good_order( this)" class="hiddenControl">&nbsp;&nbsp;

<div>
   <input id="button_NIGO" type="submit" value="Follow up" style="display:inline" onClick="return not_in_good_order( this)" class="hiddenControl">
   <input id="text_NIGO_mail" type="text" size="35" class="hiddenControl" style="display:inline">
</div>
</div>


      </form>

</body></html>
