<?php
include('../config.php');



function get_dates_from_db()
{
    $myuname = DB_UNAME;
    $mypswd  = DB_PSWD;
    $myhost  = DB_HOST;
    $dbname  = DB_NAME;


    echo "getting dates<br>";

    $connid = mysql_connect( "$myhost", "$myuname", "$mypswd")  or die("Could not connect : " . mysql_error());

    print "connected...<br>";

    $selected = mysql_select_db( "$dbname", $connid);
    if ( $selected == false)
    {
         print '<p> Database not selected</p>';
         print mysql_error();
         return;
    }
    echo "<p> Database selection succeeded<br><br>";

    $data = mysql_query( "select * from dates");

    /* one method for getting data */

    $info = mysql_fetch_array( $data);

    echo "<b>num:</b> " . $info['date_num'] . " ";
    echo "<b>date:</b> " . $info['cal_date'] . " <br>";

    mysql_free_result( $data);




    // Performing SQL query
    $query = 'SELECT * FROM dates';
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());


    echo "found: " . mysql_num_rows($result) . " rows<br>";

    // Printing results in HTML
    echo "<table>";

    echo "\t<tr>";
    echo "\t\t<td> " . mysql_field_name( $result, 0) . "</td>";
    echo "\t\t<td> " . mysql_field_name( $result, 1) . "</td>";
    echo "\t</tr>";


while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
mysql_free_result($result);




    return $info;
}
?>


<body>

Get the dates from the dbms.<br>

<?php

    $get_dates = get_dates_from_db();

    print_r( $get_dates);

?>

<br>Done dates<br>

</body>
</html>
