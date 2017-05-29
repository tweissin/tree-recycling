<?php 
require( "header.php");

?>

<?php

require_once( '../inc/rcube_shared.inc');
require( '../config.php');


if( $_SERVER['REQUEST_METHOD'] != 'POST' || !isset( $_POST['weekend']))
{
  die("invalid request");
}

$weekend = $_POST['weekend'];

// get the actual dates from the file

$date_filename = "../dates.lst";
$date_handle = fopen( $date_filename, "r");
$str = fgets( $date_handle);

list( $d1, $d2, $d3, $season) = split( ";", $str);
fclose( $date_handle);

if( $weekend == 0)  $weekend_str = $d1;
elseif( $weekend == 1)$weekend_str = $d2;
elseif( $weekend == 2)$weekend_str = $d3;

$connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD)  or die("Could not connect : " . mysql_error());

$selected = mysql_select_db( DB_NAME, $connid);
if ( $selected == false)
{
  print '<p> Database not selected</p>';
  print mysql_error();
  return;
}

$stmt = "select * from tmp_pickup where temp= 'n' and weekend='date_" . $weekend . "'";
$data = mysql_query( "$stmt");
if ( !$data)
  {
    print '<p> queryerror</p>';
    print mysql_error();
    return;
  }

echo "<script type='text/javascript'>";
echo "var weekend = " . $weekend . ";\n";

$i = 0;
echo "var pickups = [];\n";
while ($line = mysql_fetch_array($data, MYSQL_ASSOC))
  {
    echo "pickups[" . $i . "] = " . array2js( array_values( $line), "string" ) . ";\n";
    $i++;
  }

$addrs = mysql_num_rows( $data);   /* get the number of pickups for this weekend */

echo "var nPickups = " . $addrs . ";\n";

mysql_free_result($data);

/* retrieve the route names */    

$stmt = "select count(*) as n, route from tmp_pickup where temp='n' and weekend='date_" . $weekend . "' group by route";
$data = mysql_query( "$stmt");
if ( !$data)
  {
    print '<p> queryerror</p>';
    print mysql_error();
    return;
  }

$nRoutes = mysql_num_rows( $data);   /* get the number of pickups for this weekend */

$routes = Array();

$i = 0;
echo "var routes = [];\n";
echo "var disp_route = [];\n";
while ($line = mysql_fetch_array($data, MYSQL_ASSOC))
  {
    $name = $line['route'];
    if( $name == "")  $name = "unassigned";

    echo "disp_route[" . $i . "] = false;\n";
    echo "routes[" . $i . "] = ['" . $line['n'] . "','" . $line['route'] . "'];\n";

    $routes[] = Array( 'number' => $line['n'], 'name' => $name);

    $i++;
  }
mysql_free_result($data);

/* now, all the pickups are in pickups[] */

echo "var nRoutes = " . $nRoutes . ";\n";


?>

</script>




<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAADfGxmE8V_sus16AUQg920hTAFekehXX2muu8j5dUCh0Wrc-ZZBTA3sQXro5lkspS1hO9rpfXWjBJDw" type="text/javascript"></script>

<script type="text/javascript">

// arrays to hold copies of the markers and html used by the side_bar
// because the function closure trick doesnt work there

var i = 0;

var icon_files = [ 'largeTDBlueIcons', 
        'largeTDBlueRedIcons', 
        'largeTDGreenIcons', 
        'largeTDGreenRedIcons', 
        'largeTDRedIcons', 
           //        'largeTDYellowIcons', - used for unassigned pickups
        'largeTDOrangeIcons', 
        'smallSQBlueIcons', 
        'smallSQBlueRedIcons', 
        'smallSQGreenIcons', 
        'smallSQGreenRedIcons', 
        'smallSQRedIcons', 
            'smallSQYellowIcons'];


function build_table_header()
{

////alert( "build_table_header");

    // Declare variables and create the header, footer, and caption.

    var oRow, oCell;
    var i, j;

    // Declare stock data that would normally be read in from a stock Web site.
    var heading = new Array();

    heading[0] = "Icon";
    heading[1] = "#";
    heading[2] = "Route";
    heading[3] = "Trees";
    heading[4] = "Name";
    heading[5] = "Addr";
    heading[6] = "Phone";
    heading[7] = "Comments";
    heading[8] = "Email";

    // Insert a row for the heading

    var tBody = document.getElementById( "oTBody");
    oRow = tBody.insertRow(-1);

    // Insert cells into the header row.
    for (i=0; i<heading.length; i++)
    {
    oCell = oRow.insertCell(-1);
    oCell.align = "center";
    oCell.style.fontWeight = "bold";
    oCell.innerHTML = heading[i];
    }
}

function add_pickup_to_table( icon, letter, parts)
{
    var oRow, oCell;

    // Insert rows and cells into the table body

    var tBody = document.getElementById( "oTBody");
    oRow = tBody.insertRow(-1);
    oCell = oRow.insertCell(-1);   oCell.innerHTML = "<img src=" + icon.image + " height=25px>";
    oCell = oRow.insertCell(-1);   oCell.innerHTML = letter;    oCell.align = "center";
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[2];  oCell.align = "center";        // route
    oCell = oRow.insertCell(-1);   oCell.innerHTML = "(" + parts[11] + ", " + parts[12] + ")";   // trees
         oCell.style.width="40px";
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[ 6];  // name
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[ 7];  // address
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[ 10];  // phone
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[ 14];  // notes
    oCell = oRow.insertCell(-1);   oCell.innerHTML = parts[ 8];  // email
}


function process_it()
{
    // Creates a marker whose info window displays the letter corresponding
    // to the given index.

  ////alert( "process_it: nRoutes: " + nRoutes);

    function createMarker(point, name, address, phone, iRoute, index, unassigned)
    {
        //  we have only 99 icons for each color
// if( index > 99)
//     return null;

    // Create a numbered icon for this point using our icon class

    //var letteredIcon = new GIcon(baseIcon);

//alert('made it to 1');    
var letteredIcon = new GIcon(G_DEFAULT_ICON);

    letteredIcon.shadow = "mapfiles/shadow50.png";
    letteredIcon.iconSize = new GSize(20, 34);
    letteredIcon.shadowSize = new GSize(37, 34);
    letteredIcon.iconAnchor = new GPoint(9, 34);
    letteredIcon.infoWindowAnchor = new GPoint(9, 2);

    var number = index;
    var letter;
    if( number <= 99)
      letter = number.toString();
    else
      {
        var remainder = number % 10;
        var quotient = ( number - remainder ) / 10;

        // A is #65 in Unicode
        letter1 = String.fromCharCode( 65 + quotient - 10);
        letter2 = remainder.toString();
        letter = letter1 + letter2;
      }

//alert('made it to 2');    
    if( unassigned)
      letteredIcon.image = new String( "../mapfiles/unassigned/marker" + letter + ".png");
    else
      letteredIcon.image = new String( "../mapfiles/" + icon_files[ iRoute] + "/marker" + letter + ".png");
        letteredIcon.printImage = letteredIcon.image;
        letteredIcon.mozPrintImage = letteredIcon.image;


//alert('made it to 3');    
    // Set up our GMarkerOptions object

    //markerOptions = { icon:letteredIcon, title:address };
    markerOptions = { title:address };
    var marker = new GMarker(point, markerOptions);


//alert('made it to 4');    
    GEvent.addListener(marker, "click", function() {
//               marker.openInfoWindowHtml("Marker <b>" + letter + "</b>");
               marker.openInfoWindowHtml( "Name: " + name + "<br>addr: " + address + "<br>phone: " + phone);
    });
    return marker;
    }

    // loop over routes, plotting all pickups on checked route before moving on to next route

    var iMarker = 0;
    for( var iRoute = 0; iRoute < nRoutes; iRoute++)
    {
        if( nRoutes > 1 && !document.test.route[ iRoute].checked)
        continue;
        else if( nRoutes == 1 && !document.test.route.checked)
        continue;
    
    r = routes[ iRoute];

    ////alert( "Checkbox at index " + iRoute + " is checked! nTrees: " + r[0] + " route: <" + r[ 1] + ">");

    var j = 0;
    for( var i = 0; i < nPickups; i++)
        {
      parts = pickups[i];

      // parts[2] == route name

      if( parts[2] == r[ 1])    /* if this pickup is in the route we're plotting */
      {
        var lat = parseFloat(parts[3]);   // lat
        var lng = parseFloat(parts[4]);   // lng
        var point = new GLatLng( lat, lng);

        var remainder = (j+1) % 100;
        var iTree = j+1;       

        ////alert( "iMarker: " + iMarker + " iTree: " + iTree);

        var m = createMarker( point, parts[6], parts[7], parts[10], iMarker, iTree, r[1] == "");
        map.addOverlay( m);

//alert('made it to 5');    
        var letter = iTree.toString();
        var icon = m.getIcon();
        add_pickup_to_table( icon, letter, parts);
        j++;
      }
      
////alert('made it to 6');    
    }  // end the loop over lines in the address file

    if( r[1] != "")  iMarker++;   // go to the next Icon only if assigned
    
    }  // end the loop over the checked routes

}

var map = null;

// intersection of Rts 85 and 135

hop_lat = 42.228454;
hop_lng = -71.522069;

var baseIcon;


function initialize()
{
    if (GBrowserIsCompatible())
    {
        map = new GMap2(document.getElementById("map_canvas"));

        map.setCenter( new GLatLng( hop_lat, hop_lng), 12);

        map.enableScrollWheelZoom();

        map.setMapType(G_NORMAL_MAP);
        map.addControl(new GLargeMapControl());
        map.addControl(new GMapTypeControl());
    }

    // Create a base icon for all of our markers that specifies the
    // shadow, icon dimensions, etc.

    baseIcon = new GIcon(G_DEFAULT_ICON);
    baseIcon.shadow = "mapfiles/shadow50.png";
    baseIcon.iconSize = new GSize(20, 34);
    baseIcon.shadowSize = new GSize(37, 34);
    baseIcon.iconAnchor = new GPoint(9, 34);
    baseIcon.infoWindowAnchor = new GPoint(9, 2);

    build_map();
}

function add_route( i)
{
    build_map();
}

function build_map()
{
    // clean up the map and the list

    map.clearOverlays();

    var table = document.getElementById( "oTable");
    var rows = table.rows;

    while( table.rows.length)
    table.deleteRow( table.rows.length-1);


    // add the table header to the list of addresses

    build_table_header();
    process_it();

    return false;
}


</script>
<title>T4 Map Tree Routes</title>
</head>

<body onload="initialize()" onunload="GUnload()">

<?php

require( "navbar.php");

navbar(3);
?>

<h3>Map Tree Routes</h3>


<div id="header">

    <form name="test" action="map_routes.php" enctype="application/x-www-form-urlencoded" method="post">

    <div id="instructions">
       Select one or more routes to display - or, to choose another weekend, click on the link above.<br><br>
    </div>

    <b>Routes for weekend: <?php echo $weekend_str; ?></b><br>

    <div id="weekend_dates">

<?php
  for( $i = 0; $i < sizeof( $routes); $i++)
    {
      echo  '<input onclick="add_route(' . $i . ')" name="route" type="checkbox">' . 
                $routes[$i]['name'] . ' (' . $routes[$i]['number'] . ') &nbsp;&nbsp';
    }
?>


</div>  <!-- where routes are displayed -->

    </form>

</div>

<br>

<div id="map_canvas" class="map" ></div>

<br><br><br><br>


<h3>Street Addresses</h3>


<table class="address_list" id="oTable" rules="all" frame="box" cellpadding="3">
    <tbody id="oTBody"></tbody>
</table>

</body>

</html>
