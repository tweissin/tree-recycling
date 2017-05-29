<?php 
require( "header.php");

?>

<style>
INPUT {
  font-family: Verdana, Arial, sans-serif;
  font-size: 12px;
 }
.small {
  font-family: Verdana, Arial, sans-serif;
  font-size: 12px;
}

</style>

<script type="text/javascript">

var parts = [];
var lines;
var nLines;
var file_name;
var text;
var new_text;

function init()
{
    lines = "";
    nLines = 0;
    file_name = "";
    text = "";
    new_text = "";
}

<?php
require_once( '../inc/rcube_shared.inc');

$style = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $action = $_POST[ 'action'];

    if( $action == 'upd' || $action == 'list')
    {
        require( '../config.php');
	$getList = true;

	$connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

	$selected = mysql_select_db( DB_NAME, $connid);
	if ( $selected == false)
	{
	     print '<p> Database not selected</p>';
	     print mysql_error();
	     return;
	}

	/* do the update, then fall through, which causes a refresh */

	if( $action == 'upd')
	{     
	    $update_list = $_POST['pickup_list'];
	    echo "var updates = '" . $update_list . "';";

	    $s = explode( "|", $update_list);
	    foreach( $s as $value)
	    {
		list( $route, $id) = explode( ".", $value);

		mysql_query( "BEGIN") or die( "BEGIN error: " . mysql_error());

		$stmt = "update tmp_pickup set route='" . $route . "' where id='" . $id . "'";

// echo "var stmt=\"" . $stmt . "\";\n";

		$data = mysql_query( "$stmt");
		if ( !$data)
		{
		     print '<p> update error</p>';
		     print mysql_error();
		     return;
		}
		mysql_query( "COMMIT") or die( "COMMIT error: " . mysql_error());

	    }
	    /* and fall through */

            $upd_completed = true;

	}

	$weekend = $_POST['weekend'];

	echo "var weekend = " . $weekend . ";";

	$stmt = "select id,temp,route,name,street,phone from tmp_pickup where temp= 'n' and weekend='date_" . $weekend . "'";
	$data = mysql_query( "$stmt");
	if ( !$data)
	{
	     print '<p> queryerror</p>';
	     print mysql_error();
	     return;
	}

	$addrs = mysql_num_rows( $data);   /* get the number of pickups for this weekend */

	echo "var nPickups = " . $addrs . ";\n";

	//	mysql_free_result($data);
    }
}

?>

function set_change( id)
{
//  alert( "changing id: " + id + " nPickups: " + nPickups);

  document.getElementById("t_" + id).value = 'c';

//  alert( "field: " + document.getElementById("t_" + id).value);
}


function get_list()
{
    for( i = 0; i < document.my_form.map_num.length; i++)
    {
        if( document.my_form.map_num[i].checked)
            break;
    }
    document.my_form.weekend.value = i;

    document.my_form.submit();
}


function write_list()
{

// alert( "write_list: " + nPickups);

   new_text = "";
   for( i = 0; i < nPickups; i++)
   {
       if( document.getElementById("t_" + i).value != 'c')
	 continue;

       route = document.getElementById( i).value;

// alert( "route: " + route);

        // send back the unique id and the new route

        if( new_text != "") new_text += "|";

        new_text += route + "." + document.getElementById( "i_" + i).value;
   }

   //alert( new_text);

   document.test.pickup_list.value = new_text;
   document.test.list_len.value = nPickups;
   document.test.weekend.value = weekend;


}

</script>

<title>T4 - Set Pickup Routes</title>

</head>

<body onLoad="init()">
<?php

require( "navbar.php");

navbar(2);
?>

<h3>Set Tree Routes</h3>

<form name="my_form" action="set_routes.php" enctype="application/x-www-form-urlencoded" method="post">
Assign route names to weekend pickup lists.  To retrieve pickups for a weekend, choose a weekend.

<input type="hidden" name="action" id="action" value="list">

<div id="weekend" style="margin:10px">
  <table>
    <tr><td colspan=3 valign="top"><b>Weekend</b></td></tr>
    <tr>
        <td><input name="map_num" type="radio" value="0" onclick="get_list()">1st</td>
        <td><input name="map_num" type="radio" value="1" onclick="get_list()">2nd</td>
        <td><input name="map_num" type="radio" value="2" onclick="get_list()">3rd</td>
    </tr>
 </table>

 <input name="weekend" id="weekend" type="hidden">

</div>
</form>



<?php if( isset( $getList)) { ?>

<div style="width: 60%">

<form name="test" action="set_routes.php" enctype="application/x-www-form-urlencoded" method="post">

<?php
   if( isset( $upd_completed))
     echo "<span style='color:red'>Updated completed successfully.</span><br>";
?>

    Make (additional) modifications to route assignments or navigate elsewhere using the workflow indicator above.<br>
    When finished, click <input id="button" onClick="write_list()" TYPE="submit" VALUE="Save List"> to save the file on the website.

    <input type="hidden" name="action" id="action" value="upd">

    <input id="pickup_list" name="pickup_list" type="hidden">
    <input id="list_len" name="list_len" type="hidden">
    <input name="weekend" id="weekend" type="hidden">

</form>

<?php

    echo "<div id='message' style='margin-top: 10px; overflow: auto; height: 300px; border: 1px solid gray; padding: 5px;'>\n";
    echo "<form name='pickups'><table cellpadding='1' cellspacing='0'>\n";

    $i = 0;
    while ($line = mysql_fetch_array($data, MYSQL_ASSOC))
    {
	$j = $i + 1;

	echo "<tr><td><input name='addr' id='" . $i . "' maxlength='2' size='1' type='text' value='" . $line[ 'route'];
	echo "' onchange='set_change(this.id)'></td>\n";
	echo "<input name='temp' type='hidden' id='t_" . $i . "' value='n'>\n";
	echo "<input name='ids' type='hidden' id='i_" . $i . "' value='" . $line['id'] . "'>\n";
	echo "<td width=10px align='right'>" . $j . ". </td><td class='small'>" . $line['name'] . "</td>\n";
	echo "<td class='small'>&nbsp;&nbsp;&nbsp;" . $line['street']  . "</td>\n";
	echo "<td class='small'>&nbsp;&nbsp;&nbsp;" . $line['phone']  . "</td></tr>\n";

	$i++;
    }

    echo "</table></form>";
    echo "</div>";

}

?>
</div>
</body>
</html>
