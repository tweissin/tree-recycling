<?php

require( '../../config.php');

function execute_update($set, $id) {
    $result = mysql_query("UPDATE tom_tmp_pickup SET " . $set . " where id=" . $id);
    if (!$result) {
        echo 'Could not update pickup: ' . mysql_error();
        exit;
    }
}

function update_pickup_info($id, $address, $zone, $route_order)
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

    if ($address!=null) {
        execute_update("address = '" . $address . "'", $id);
    }
    if ($zone!=null) {
        execute_update("zone = " . $zone, $id);
    }
    if ($route_order!=null) {
        execute_update("route_order = " . $route_order, $id);
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
$address = $array['address'];
$zone = $array['zone'];
$route_order = $array['route_order'];

update_pickup_info($id, $address, $zone, $route_order);
