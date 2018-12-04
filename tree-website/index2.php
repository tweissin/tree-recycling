<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">

<html>

<head>

  <meta content="0" http-equiv="expires">
  <meta content="no-cache" http-equiv="pragma">
  <meta content="no-cache" http-equiv="pragma">
  <meta name="description" content="Boy Scout Troop 4, Hopkinton, MA">
  <meta name="keywords"  content="Troop 4, Christmas Tree Pickup, Boy Scouts, Hopkinton, ma">
  <meta name="author"    content="Peter Dittman">
  <meta name="copyright" content="">
  <meta name="language"  content="English">
  <meta name="charset"   content="ISO-8859-1">
  <meta name="rating"    content="Restricted">
  <meta name="expires"   content="Never">
  <meta name="robots"    content="Index, Follow">
  <meta name="distribution"  content="Global">
  <meta name="revisit-after" content="7 Days">
  <meta name="MSSmartTagsPreventParsing" content="TRUE">
    <link rel="stylesheet" href="style.css">
<!--  <base href="http://troop4hopkinton.org/trees_home/" /> -->

<title>Troop 4 Hopkinton, Christmas Tree Pickup Service</title>

<?php
define( 'ENTER_ADDRESS', 'Click the \'Locate Me\' button to confirm your location.');
define( 'CONF_ADDRESS', 'Address Located - Proceed to Step 3.');

define( 'HILITE_NOTE', 'border-style: solid; border-color: black; border-width: 1px; padding: 2px; margin-bottom: -10px; color: black');
define( 'NOTE', 'border-style: solid; border-color: black; border-width: 1px; padding: 2px; margin-bottom: -10px; color:black');

$date_filename = "dates.lst";

$table_info[] = Array();

$date_handle = fopen( $date_filename, "r");
$str = fgets( $date_handle);

//echo $str . "<br>";

$dts[] = Array();
list( $dts[0], $dts[1], $dts[2], $season, $closed_str) = split( ";", $str);
$closed = $closed_str == "closed";

for( $i = 0; $i < 3; $i++)
{
    $parts = split( "/", $dts[$i]);
    $table_info[$i]['date'] = $parts[0];
    if (count($parts)==2) {
        $table_info[$i]['closed'] = $parts[1];
    }
}
fclose( $date_handle);

if( !$closed || isset( $_GET['open'])) {

?>


<STYLE TYPE="text/css">
#display {width:500px; border:10px ridge blue; padding:20px}

.hiddenControl {visibility:hidden}
.displayControl {visibility:visible}
</STYLE>


<script type="text/javascript" src="tooltip.js" language="JavaScript"></script>
<script language="JavaScript">

var FiltersEnabled = 0 // if your not going to use transitions or filters in any of the tips set this to 0

tip_txt=["Why is Submit disabled?", "In order to enable the Submit button, you must first confirm your address - the location of the tree pickup.  Please scroll up to the map area, enter your street address then select the 'Locate Me' button. "];
tip_style=["white","black","#999999","#CCCCCC","","","","","","","right","","","",400,"",2,2,10,-100,"","","","",""];

applyCssFilter();

</script>



<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=AIzaSyDCnEZxN2selIUgvRhwtI5DrPLtRrD9HKI" type="text/javascript"></script>

<script type="text/javascript">

var map = null;
var geocoder = null;

// intersection of Rts 85 and 135
hop_lat =42.228454;
hop_lng =-71.522069;

function initialize()
{
    if (GBrowserIsCompatible()) {

        map = new GMap2(document.getElementById("map_area"));
        map.setCenter( new GLatLng( hop_lat, hop_lng), 12);

        map.enableScrollWheelZoom();

        map.setMapType(G_NORMAL_MAP);
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
        geocoder = new GClientGeocoder();
    }
}

var marker;
var is_set = 0;

var save_address;

var reasons=[];
reasons[G_GEO_SUCCESS]            = "Success";
reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address.";
reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address.";
reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address.";
reasons[G_GEO_BAD_KEY]            = "Bad Key.";
reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Queries.";
reasons[G_GEO_SERVER_ERROR]       = "Server error.";


var found = 0;
var user_confirmed = false;

function set_status( type)
{
    found = 0;
    user_confirmed = false;

    if( type == 1)
    {
        document.getElementById("confirmed_address").className = "hiddenControl";
        document.getElementById("confirmed_address").innerHTML = "When finished moving the marker, click the 'Confirm Address' button then the 'Submit' button to complete your request.";

	document.getElementById("confirmed_address").style.borderColor = "black";
	document.getElementById("confirmed_address").style.color = "black";

	document.getElementById("submit_button").disabled = false;
	document.getElementById("submit_help").className = "displayControl";
    }
    document.getElementById("confirm_address_button").className = "hiddenControl";

    document.getElementById( "locate_me_button").disabled = false;
}


function reset_form()
{
    found = 0;
    user_confirmed = false;
    document.getElementById("confirmed_address").innerHTML = "<?php echo ENTER_ADDRESS ?>";
    document.getElementById("confirmed_address").style.borderColor = "black";
    document.getElementById("confirmed_address").style.color = "black";

    document.getElementById("confirm_address_button").className = "hiddenControl";
    document.getElementById("locate_me_button").className = "displayControl";
    document.getElementById( "locate_me_button").disabled = true;
    document.getElementById("submit_button").disabled = false;
    document.getElementById("submit_help").className = "displayControl";


    if( is_set)
    {
         map.removeOverlay(marker);
         map.setCenter( new GLatLng( hop_lat, hop_lng), 12);
    }
}

function set_confirmed()
{
    user_confirmed = true;

    document.getElementById( "submit_button").disabled = false;
    document.getElementById("submit_help").className = "hiddenControl";


    document.getElementById("confirmed_address").innerHTML = "<?php echo CONF_ADDRESS ?>";
    document.getElementById("confirmed_address").style.borderColor = "black";
    document.getElementById("confirmed_address").style.color = "black";
}

function go_home()
{
    if (!document.getElementById("name").value)
    {
            alert ("Please enter your name in the name field");
             return (false);
    }

    if (!document.getElementById("address").value)
    {
            alert ("Please enter your address in the address field");
             return (false);
    }

    if (!document.getElementById("email").value)
    {
            alert ("Please enter your email in the email field");
             return (false);
    }

    if (!document.getElementById("phone").value)
    {
            alert ("Please enter your phone in the phone field");
             return (false);
    }

    checked = false;
    for( i = 0; i < 3; i++)
    {
      if( document.getElementById( "d" + i).checked)
	checked = true;
    }

    if( !checked)
    {
      alert( "Please select a pickup date");
      return false;
    }
    return true;
}

function locate_me()
{
    x = document.getElementById("address");

    save_address = String( x.value + " hopkinton, MA");
    address = save_address;

//    alert( address);
//    alert( "Is set: " + is_set);

    // ====== Perform the Geocoding ======        
    geocoder.getLocations( address, function (result)
       { 
            // If that was successful
            if (result.Status.code == G_GEO_SUCCESS)
            {
                for (var i=0; i<result.Placemark.length; i++) {
                    var p = result.Placemark[i].Point.coordinates;
                }

		var point = new GLatLng( p[1], p[0]);

		map.setCenter(point, 15);
		if( is_set) map.removeOverlay(marker);

		marker = new GMarker(point, {draggable: true});
		map.addOverlay(marker);

		is_set = 2;
		found = 1;

                // get the string representation, tweeze it apart                
                var point1 = marker.getLatLng().toUrlValue(6);
                var comma = point1.indexOf( ",", 0);

                document.getElementById( "addr_lng").value = point1.slice( comma+1, point1.length);
                document.getElementById( "addr_lat").value = point1.slice( 0, comma);

		document.getElementById("confirmed_address").className = "displayControl";
                set_confirmed();


/*                GEvent.addListener(marker, "dragend", function() {
		    marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
                   });
*/

                GEvent.addListener(marker, "click", function() {
                    marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
                      });

//                GEvent.trigger(marker, "click");


		// add a drag-end listener - this way, even if they position themselves and decide
		// to move the pushpin, we'll get the correct pin location

                GEvent.addListener(marker, "dragend", function() {

                    // get the point and extract the lat/lng

                // get the string representation, tweeze it apart                
                var point1 = marker.getLatLng().toUrlValue(6);
                var comma = point1.indexOf( ",", 0);

                document.getElementById( "addr_lng").value = point1.slice( comma+1, point1.length);
                document.getElementById( "addr_lat").value = point1.slice( 0, comma);
		is_set = 1;

		/* unhide the button so they can tell us they confirmed their address */

                set_status( 1);
		document.getElementById("confirm_address_button").className = "displayControl";
		document.getElementById("confirmed_address").className = "displayControl";
		document.getElementById("locate_me_button").className = "hiddenControl";


		  });

            }

	    // ====== Decode the error status ======
	    else
	    {
		var reason = "Code " + result.Status.code;
		if (reasons[result.Status.code]) {
		    reason = reasons[result.Status.code]
		} 

		alert( "Could not find " + "\"" + address + "\"; Reason: " + reason + "\n\nPlease position the marker to closest street on the map and click the \"Confirm Address\" button to confirm.");

		var center = new GLatLng( hop_lat, hop_lng);
		map.setCenter( center, 13);

		if( is_set) map.removeOverlay(marker);

		marker = new GMarker( center, {draggable: true});
		map.addOverlay(marker);

                // set the default point

                var point1 = marker.getLatLng().toUrlValue(6);
                var comma = point1.indexOf( ",", 0);
                document.getElementById( "addr_lng").value = point1.slice( comma+1, point1.length);
                document.getElementById( "addr_lat").value = point1.slice( 0, comma);

                // and when they're done positioning, make sure we grab the lat/lng

                GEvent.addListener(marker, "dragend", function() {

                    // get the point and extract the lat/lng

                // get the string representation, tweeze it apart                
                var point1 = marker.getLatLng().toUrlValue(6);
                var comma = point1.indexOf( ",", 0);

                document.getElementById( "addr_lng").value = point1.slice( comma+1, point1.length);
                document.getElementById( "addr_lat").value = point1.slice( 0, comma);

//alert( "lat: " + document.pickup.addr_lat.value + ", lng: " + document.pickup.addr_lng.value);

//		    marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));

                   });

                GEvent.addListener(marker, "click", function() {
                    marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
                      });

		is_set = 1;

		/* unhide the button so they can tell us they confirmed their address */

                set_status( 1);
		document.getElementById("confirm_address_button").className = "hiddenControl";
		document.getElementById("confirmed_address").className = "displayControl";
		document.getElementById("locate_me_button").className = "displayControl";

		document.getElementById("confirmed_address").innerHTML = "<?php echo ENTER_ADDRESS ?>";
		document.getElementById("confirmed_address").style.borderColor = "black";
		document.getElementById("confirmed_address").style.color = "black";

	    }
        } 
    );
}

</script>


</HEAD>

<body onload="initialize()" onunload="GUnload()"
    background="images/nwsletter.gif" bgcolor="#ffffff" text="#000000">

<DIV id="TipLayer" style="visibility:hidden;position:absolute;z-index:1000;top:-100"></DIV>


<table width=100%>
  <tr>
    <td valign="top" height="30px">
         <a href="http://troop4hopkinton.org"><img src="images/troop4_home.jpg"></a></td>
    <td width=3%></td>
    <td>
        <span style="color:red; font-weight: bold; padding-top: -15px; font-family: Comic Sans MS; font-size: 28px;">
Boy Scout Troop 4 - Christmas Tree Pickup Service
<br><span style="font-size:18px"><?php echo $season ?></span></span>

    </td>
  </tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width=700>
  <tbody>
  <tr valign="top"> 
    <td>
	<img src="images/onetree.gif" border="0" width="100px">
	<br>
	<br>
    </td>

    <td width=15>&nbsp</td>

    <td align="left">
      <div id="text" style="font-family: Verdana; padding: 3px; ">

      <br>

          <?php
          $too_many = false;
          if ($too_many) {
          ?>
          <span style="color:red; font-weight: bold; font-family: Comic Sans MS; font-size: 18px;">
Thanks very much  for your interest in Christmas Tree collection/recycling and for supporting our local Scouts.  Unfortunately we have reached the maximum number of trees we can manage and are not taking new requests for January 14, 2017.<br>
</span>
          <?php
          }
          ?>

          <span style="color:black; font-weight: bold; font-family: Comic Sans MS; font-size: 18px;">
       This service is ONLY available to households in Hopkinton, MA.

      <br>


      <br>

      To request a pickup, please follow the 3 steps listed below.
      </span>

      <br><br>
      <b>NOTE: New requests will be shut down 3 days before actual pickup to give time for route planning.

      <br><br>
      <span style="color: #006600; font-weight: bold;">

      Please have your tree(s) curbside before 8AM on the date of your
      scheduled pickup<br> - pickup begins at <span style="text-decoration: red;"><span style="text-decoration: underline;"> 8AM</span></span.<br><br>
      </span>

      <span style="color:black; font-weight: bold; font-family: Comic Sans MS; font-size: 18px;">
      The cost of the service is $15 for each tree.  <br>
      </span>

      <span style="color: #006600; font-weight: bold;">
      - Attach cash or 
      check, made out to "<i>BSA - Troop 4</i>", 
      to the tree (a ziploc baggy works best).<br><br>

      <b>PLEASE DO NOT LEAVE ANY OF THE FOLLOWING: <br>
      - NO TREES IN PLASTIC BAGS<br>
      - NO WREATHS<br>
      - NO GARLAND
      </b> </span>

      <br><br>

      If you have specific questions about a pickup or our service, please  
      <a class="troop4" href="mailto:tree-request@troop4hopkinton.org?subject=Tree Pickup Question"><font size=2>send us an email</font></a>.

      <br><br>
      Thank you for your support of Scouting and Troop 4 in Hopkinton.
      <br><br>

<!--      <div id="season" style="float:right;"></div> -->

      </div>
    </td>

  </tr>

<!-- and draw the main body -->

<tr>

   <td width="150"></td>
   <td width=15>&nbsp<br></td>

   <td>

   <form action="action.php?type=n" name="pickup" onsubmit="return go_home();" enctype="application/x-www-form-urlencoded" method="post">

    <table border="0" cellpadding="0" cellspacing="5" width="700">

    <tr>
      <td align="left" valign="middle">
      <span style="color: #006600; font-weight: bold; font-size: 36px;">
	Step
      </span>
      </td>
      <td align="left" valign="top">
        <img src="images/Step1.jpg" border="0" width="100px">
      </td>
      <td colspan="3" align="left" valign="middle">
      <b>Enter your name, address, tree quantity information and <br>select a pickup date below then Proceed to Step 2</b>
      </td>
    </tr>

    </table>
    <table border="0" cellpadding="0" cellspacing="5" width="700">

    <tr>
      <td colspan=2 align="left" valign="top">

	 <table class="small-body" cellpadding="0" cellborder="0">
           <tbody>
           <tr><td>Name:</td><td>
                <input name="name" value="" type="text" size="45" id="name" /></td></tr>
           <tr><td>Address:</td><td>
                <input name="address" value="" type="text" size="45" id="address" onblur="set_status( 0);" /></td></tr>
           <tr><td>Email Address:</td><td><input name="email" value="" type="text" size="45" id="email" /></td></tr>
           <tr><td>Phone:</td><td><input name="phone" value="" type="text" size="45" id="phone" /></td></tr>

	   <tr><td colspan=2><br><b>Which pickup date do you prefer?</b></td></tr>

           <tr><td>

<table width=120px>

<?php
for( $i = 0; $i < 3; $i++)
{
  $s1 = "";
  $s2 = "";
  if( $table_info[$i]['closed'] == 'c')
  {
     $s1 = " style='visibility: hidden'";
     $s2 = " style='text-decoration:line-through'";
  }

  echo "<tr><td width=10px><input type='radio' name='date' id='d" . $i . "' value='date_" . $i . "' class='checkbox'" . $s1 . "></td>\n    <td>";

  echo "<span " . $s2 . "> " . $table_info[$i]['date'] . "</span></td></tr>\n";
}
?>

</table>
          </td></tr>

<!--
          <tr>
            <td colspan=2 valign="top"><font face="arial" style="color: #006600; font-weight: bold;" size="-1">
      NOTE: Requests will not be accepted after 9 am the day before the scheduled pickup. 

               <br>
               <br>
          </td></tr>
-->
          <tr>
            <td colspan=2 valign="top"><font face="arial" size="-1"><b>Number of Trees to pickup</b>
	      <select name="small" value="" size="1" />
		<option selected value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12">12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
		<option value="17">17</option>
		<option value="18">18</option>
		<option value="19">19</option>
		<option value="20">20</option>
		<option value="21+">21+</option>
	      </select>
	      <input name="large" value="" type="hidden" size="10" id="large" />
             </td>
<!--
            <td colspan=2 valign="top"><font face="arial" size="-1"><b>Trees Under 8' </b>
	      <select name="small" size="1">
		<option selected value=""></option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5+">5+</option>
	      </select>

              <font face="arial" size="-1"><b>Trees Over 8' </b>
	      <select name="large" size="1">
		<option selected value=""></option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5+">5+</option>
	      </select>
           </td>
-->
         </tr>
         <tr>
	   <td colspan=2 valign="top"><font face="arial" size="-1">
	     <input name="source" value="other" type="hidden">
<!--
	     <br><b>How did you hear about our service?</b><br>
	     <input name="source" value="newspaper" type="radio" class="checkbox" checked="checked">An article in the newspaper<br>
	     <input name="source" value="flyer/poster where I bought the tree" type="radio" class="checkbox">Flyer or poster where I bought my tree<br>
	     <input name="source" value="from a friend" type="radio" class="checkbox">From a friend<br>
	     <input name="source" value="other" type="radio" class="checkbox">other
-->
	     </font>
	   </td>
         </tr>

         <tr>
	   <td colspan=2 valign="top"><font face="arial" size="-1">
	      <br><b>Would you like an email confirmation?</b><br>
	      <input type="radio" name="email_confirm" value="Yes" class="checkbox">Yes
	      <input type="radio" name="email_confirm" value="No" checked="checked" class="checkbox">No
	     </font>
          </td>
         </tr>

         </table>

       </td>

    </tr>

    </table>
    <br><br>
    <table border="0" cellpadding="0" cellspacing="5" width="700">

    <tr>
      <td align="left" valign="middle">
      <span style="color: #006600; font-weight: bold; font-size: 36px;">
	Step
      </span>
      </td>
      <td align="left" valign="top">
	<img src="images/Step2.jpg" border="0" width="100px">
      </td>
      <td colspan="3" align="left" valign="middle">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
    </tr>

    </table>
    <table border="0" cellpadding="0" cellspacing="5" width="700">

    <tr>
       <td width="400" valign="top">

	    <!-- status div for address confirmation -->

	    <div id="confirmed_address" style="<?php echo HILITE_NOTE?>" class="displayControl" onload="set_status();">
            <b><?php echo ENTER_ADDRESS ?></b>
            </div><br>


<a onClick="locate_me( this)"><button type="button" id="locate_me_button" disabled>Locate Me!</button></a>
	    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a onClick="set_confirmed()"><button type="button" id="confirm_address_button" class="hiddenControl">Confirm Address</button></a>

            <div id="map_area" style="width: 400px; height: 350px"></div>
       </td>

    </tr>

    </table>
    <br><br>
    <table border="0" cellpadding="0" cellspacing="5" width="700">

    <tr>
      <td align="left" valign="middle">
      <span style="color: #006600; font-weight: bold; font-size: 36px;">
	Step
      </span>
      </td>
      <td align="left" valign="top">
	<img src="images/Step3.jpg" border="0" width="100px">
      </td>
      <td colspan="3" align="left" valign="middle">
      <b>If you have any comments please add them below and <br>Click the Submit button</b>
      </td>
    </tr>

    </table>
    <tr>
	<td colspan="3" align="center" valign="top">
<!--
	  <font face="arial" size="-1">
	  <br><b>If you have any comments please add them below: </b></font>
-->
          <br>
	  <textarea cols="50" rows="5" name="comments"></textarea>
	  <p>
          <input id="submit_button" value="Submit" type="submit">
	  <input value="Clear the form" type="reset" onclick="reset_form()">
<!--
<div id="submit_help" style="display:inline">
            <a href='#' onMouseOver='stm( tip_txt, tip_style);' onMouseOut='htm()' style="color:gray">Why is submit disabled?</a>
</div>
-->
	  </p></td>
      </tr>
    </tbody>
  </table>

  <!-- fields to send the lat/lng -->

  <input id="addr_lng" type="hidden" name="addr_lng" />
  <input id="addr_lat" type="hidden" name="addr_lat" />


</form>

</tr>
</table>

<?php

} /* end the "open" page, begin what's displayed when closed. */

else  {

?>

<body background="images/nwsletter.gif" bgcolor="#ffffff" text="#000000">

<p class="header" align="center"></p>

<div id="closed_body" style="margin-bottom: 20px">
<br>&nbsp;<br>

<b>Thank you</b> very much for your interest and support of Boy Scout
Troop 4 and our Christmas Tree pickup service.  We have concluded our
service for the <?php echo $season ?> season and would invite you back again
next year.

<br><br>

<table width="100%">
<tbody>

<tr><td width="50%">
Please consider reading more about our troop <a href="http://www.troop4hopkinton.org">here</a>

<br><br>
<font color=red>Good health and much happiness in the New Year!</font>

&nbsp;<br><br>

Read what people had to say about our service!
<br>

    <table border="0" cellpadding="5" cellspacing="2">
    <tbody>

    <tr><td width="3%">&nbsp;</td><td valign="top">AC</td><td><font size="2">Love this service!!! Thank you!!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">TO</td><td><font size="2">We use you guys every year. Great service from a great troop!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">JM</td><td><font size="2">Thank you for providing such a great service!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">LB</td><td><font size="2">Love this service. Thank you Boy Scouts!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">PM</td><td><font size="2">Thank you very much for providing this terrific service! Best regards</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">DC</td><td><font size="2">Thanks for this service every year - happy holidays!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">ST</td><td><font size="2">Thank you guys so much! We always really appreciate this service!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">MQ</td><td><font size="2">As always Thank you for offering this service. It’s a huge help for us each holiday season!</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">AL</td><td><font size="2">Great service. Thank you for offering it to residents.</font></td></tr>
    <tr><td width="3%">&nbsp;</td><td valign="top">SM</td><td><font size="2">Keep up the good work Troop 4 !!!</font></td></tr>
    </tbody>
    </table>

</td>


<td valign="top">
<center><br>&nbsp
<img src="images/trees.jpg" border=3 style="vertical-align: top; width:80%"></center>
</td>

</tr>
</tbody></table>

</div>

<?php } ?>

<div id="footer">

<div class="left" style="font-size: 80%">&copy;2007-2018 <a href="http://stillwaterssoftware.com">Still Waters Software</a></div>

</div>

</body></html>
