<?php 
require( "header.php");


function get_count( $temp)
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

    $pred = $temp == 'all' ? "" : "where temp = 'y'";
    $sql = mysql_query( "select count(*) as nrec from tmp_pickup " . $pred);

    $row = mysql_fetch_array($sql);
    return $row['nrec'];
}

$n_items = get_count('temp');
$n_total = get_count( 'all');


?>

<title>T4 - Tree Pickup Administration</title>
</head>
<body onLoad="init()">
<?php

require( "navbar.php");
navbar(0);

?>

<h3>Tree Service Administration</h3>

<p>There are two primary activities involved in managing the chrismas tree pickups:</p>

<ol>
  <li>Regular review of the incoming pickup requests for validity (valid/invalid email addresses, valid location, etc.)</li>
  <li>Each friday night preceding a pickup weekend, assign routes, print out maps/lists for drivers, and disable any additional requests for that weekend</li>
</ol>

<p>This site administration area is designed to assist you in those tasks.</p>

<div style="width: 70%">

  <h4>Regular Pickup Request Review  (<strong><font color="red"><?php echo $n_items;?></font></strong> requests to be reviewed; out of <strong><font color="red"><?php echo $n_total;?></font></strong> total requests)</h4>
  <p>Throughout the holiday season, residents enter their pickup requests on the public
  website.  Those requests are saved for later review.  The 
<a href="process_list.php">Review Requests</a> webpage is used to review the 
pickup requests for completeness,
to ensure there's enough information to locate the tree(s), and contact the resident
if needed.  </p>

<p>The review process is an on-going activity (pickup
requests can be submitted at anytime, from Thanksgiving
right up through the end of the pickup weekends).  It's best 
to not leave that review to the evening prior to a pickup day.  There can be
upwards of 150 pickup requests in a given weekend.</p>

<h4>Friday Preceding Pickup Saturdays</h4>

<p>On the Friday preceding pickups, you'll want to disable additional pickup requests on the website then, once done, you'll need to map the routes.</p>

<p>Use the separate page: <a href="summary.php">date management</a> to disable additional pickup requests.</p>

<p>On the night preceding a pickup weekend, pickup routes have to be assigned
  so drivers know the locations of their trees.  Before assigning routes, 
you need to know the number of trailers, and
the rough tree capacity of each trailer.  Having this information will help you better tailor
routes to trailors.</p>

<p>With the trailer information, you allocate specific routes using both the 
<a href="set_routes.php">Assign Routes</a> webpage and the 
<a href="map_routes.php">Map Routes</a> webpage to assign and view the routes. </p>



<h4>End of Season</h4>

<p>At the end of the season, the website needs to be 'shutdown' - replacing
the pickup request page with a general thank you page to the town for their
generosity.  This task is usually done on the friday night preceding the last
pickup weekend.</p>

<p>To replace the pickup page with the thank you page, click here (TBD!!!)</p>


<h4>Summary and Random Notes</h4>

<ul>
<li>Surprisingly, when navigating a route, the bulk of the time spent on a 
route is typically the time to drive to/from the tree drop-off point. </li>

<li>
<p>There are three separate web pages (at the top of the page) that you'll interact with - shown at the top of this page:</p>

<div style="width:80%; margin-left: 15px">

<dl>
<dt><strong>Review Requests</strong></dt>
<dd>Go through the incoming requests, reviewing them for correctness - both the pickup address, and any other special requests/comments.  If one was requested, send a confirmation email.  </dd>
<dt><strong>Assign Routes for a pickup weekend</strong></dt>
<dd>Assign a route code to each pickup request.  What works best is to use a letter to denote a driver/trailer pair, and use a number for a specific route for that driver/trailer.  For instance, Route 'A3' might indicate the 3rd route for driver 'A'; route 'C2' would indicate the 2nd route for driver 'C'.</dd>
<dt><strong>Map Routes</strong></dt>
<dd>View the routes that have been defined, and the pick ups that are still to be assigned to a route.  Routes are plotted using Google Maps.</dd>
</dl>

</div>
</ul>

</div>

</body>
</html>

 
