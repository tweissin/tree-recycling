<?php

require( '../../config.php');

function update_pickup_state($id, $status, $driver)
{
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());
    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
        print '<p> Database not selected</p>';
        print mysql_error();
        return;
    }

    $result = mysql_query( "select count(*) as cnt from tom_tmp_pickup where id=" . $id);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    if ($row["cnt"]!=1)
    {
        error_exit('no such pickup with that id', $id);
    }

    $stmt = "UPDATE tom_tmp_pickup SET status = '" . $status . "', driver = '" . $driver . "' where id=" . $id;
    $result = mysql_query( "$stmt");
    if (!$result)
    {
        echo 'Could not update pickup: ' . mysql_error();
        exit;
    }
}

function error_exit($message, $code) 
{
    header('HTTP/1.1 500 Internal Server Booboo');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => $message, 'code' => $code)));
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);
$id = $array['id'];
if ($id==null) {
    error_exit('must provide id', 1337);
}
$status = $array['status'];
if ($status==null) {
    error_exit('must provide status', 1337);
}
$driver = $array['driver'];
if ($driver==null) {
    error_exit('must provide driver', 1337);
}

update_pickup_state($id, $status, $driver);