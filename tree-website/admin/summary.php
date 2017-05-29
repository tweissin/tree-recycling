<?php
require( "header.php");

require( '../config.php');

$date_filename = "../dates.lst";

if( isset( $_POST['update']) )
{
    $s = "";
    for( $i = 0; $i < 3; $i++)
    {
	if( $i != 0)
	  $s .= ";";

	$cls = "close_" . $i;
	$d   = "date_" . $i;

	$s .= $_POST[$d];
	if( isset( $_POST[ $cls]))
	    $s .= "/" . "c";
    }  
    $s .= ";" . $_POST['season'];

    if( isset( $_POST['closed']))
      $s .= ";closed";
    else
      $s .= ";open";

    echo "update dates: " . $s . "<br>";

    $date_handle = fopen( $date_filename, "w");
    fputs( $date_handle, $s);
    fclose( $date_handle);
}

$table_info[] = Array();

$date_handle = fopen( $date_filename, "r");
$str = fgets( $date_handle);

//echo $str . "<br>";

$dts[] = Array();
list( $dts[0], $dts[1], $dts[2], $season, $closed) = split( ";", $str);
$closed_checked = $closed == "closed" ? "checked" : "";

//echo $dts[0] . " " . $dts[1] . " " . $dts[2] . " " . $closed_checked . "<br>";

for( $i = 0; $i < 3; $i++)
{
    list( $table_info[$i]['date'], $table_info[$i]['closed']) = split( "/", $dts[$i]);
}
fclose( $date_handle);

for( $i = 1; $i < 4; $i++)
  $table_info[ $i-1]['weekend'] = $i;

$connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

$selected = mysql_select_db( DB_NAME, $connid);
if ( $selected == false)
{
  print '<p> Database not selected</p>';
  die( mysql_error());
}

$sql = mysql_query( "select weekend, count(*) as n from tmp_pickup where temp= 'y' group by weekend order by weekend");
for( $i = 0; $line = mysql_fetch_array($sql, MYSQL_ASSOC); $i++)
{
  $table_info[$i]['to_be_reviewed'] = $line['n'];
  if( $i == 2)  break;
}
mysql_free_result($sql);

$sql = mysql_query( "select weekend, count(*) as n from tmp_pickup group by weekend order by weekend");
for( $i = 0; $line = mysql_fetch_array($sql, MYSQL_ASSOC); $i++)
{
  $table_info[ $i]['total'] = $line['n'];
  if( $i == 2)  break;

}
mysql_free_result($sql);

?>

<style>
input
{
   font-size: 11px;
   font-family: Comic Sans MS; 

}

.dates td {padding: 0 20px; margin: 0; height: 12px;}
.dates tr {padding: 0; margin: 0; height: 12px;}
.dates table
{
   font-size: 11px;
   font-family: Comic Sans MS; 
}
.dates th
{
  vertical-align: bottom;
  border-bottom: 1px solid black;
}

</style>

<title>Tree Pickup - Date Management</title>
</head>

<body>
<?php

require( "navbar.php");
navbar(0);

?>

<h3>Tree Service Administration</h3>

<p>This page is used to manage the 'seasonal' information - information that is displayed on the main pickup request page that informs the visitor of the pickup dates, opening/closing the pickup pages, etc.</p>

<b><i>Seasonal Management</i></b>




<b><i>Pickup date management</i></b>

<div style="width: 70%">

<p>Enter the season's name, the dates for weekend pickup, or for pickup weekends that have past, mark those weekends as 'closed' - this will disable those dates from appearing on the tree pickup request form.  Click update when finished.</p>

<form name="table" action="" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="update" value='1'>

<table>
<tr>
   <td><b>Season:</b></td>
   <td><input name="season" value="<?php echo $season?>"></td>
   <td width="20px"></td>
   <td>
<input name="closed" type='checkbox' value='<?php echo $closed == "closed" ? 1 : 0?>' <?php echo $closed_checked?>>Season Closed</td>

</tr>
</table>


<br><br>

<table class="dates" cellpadding=0 cellspacing=0 style="margin-bottom: 10px">
<tr class="dates">
<th class="dates">Weekend</th>
<th class="dates">Date</th>
<th class="dates">Total #</th>
<th class="dates"># to <br>Review</th>
<th class="dates">Close?</th>
</tr>


<?php
$total = 0;
for( $i = 0; $i < 3; $i++)
{
    echo "<tr class='dates'>";
    echo "<td class='dates' align=center>" . $table_info[$i]['weekend'] . "</td>";
    echo "<td class='dates' align='left'><input name='date_" . $i . "' value ='" . $table_info[$i]['date'] . "'></td>";
    echo "<td class='dates' align='center'>" . $table_info[$i]['total'] . "</td>";
    echo "<td class='dates' align='center'>" . $table_info[$i]['to_be_reviewed'] . "</td>";

    $total += $table_info[$i]['total'];

    $chk = "";
    if( $table_info[$i]['closed'] == "c")
      $chk = "checked";

    echo "<td class='dates'><input name='close_" . $i . "' type='checkbox' value='" . $i . "' " . $chk . "></td>";
    echo "</tr>\n";
}
echo "<tr><td class='dates'>&nbsp</td><td class='dates' align='right'>Total</td><td class='dates'>$total</td><td class='dates'></td></tr>";

?>
</table>

<input type=submit value="update">
<input type=reset value="reset">

</form>

</div>

</body>
</html>

 
