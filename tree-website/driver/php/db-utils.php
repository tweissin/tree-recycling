<?php
require( '../../config.php');

function get_rows($table_name)
{
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());

    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
        print '<p>Database not selected</p>';
        print mysql_error();
        return array();
    }

    $rows = array();
    $result = mysql_query( "select * from " . $table_name);
    while ($row = mysql_fetch_assoc($result)) {
        array_push($rows, $row);
    }
    return $rows;
}

