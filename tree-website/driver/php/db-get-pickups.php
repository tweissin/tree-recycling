<?php
/**
 * This is used to get a list of pickup requests.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

header("Content-Type: application/json");

$desiredFields = array(
    "id",
    "name",
    "street",
    "comments",
    "status",
    "zone",
    "route_order",
    "weekend",
    "driver",
    "email",
    "phone",
    "address"
);
$data = get_rows("tom_tmp_pickup",$_GET["dt"]=="true", $desiredFields);
print json_encode($data);
