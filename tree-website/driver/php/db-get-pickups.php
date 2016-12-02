<?php
require('db-utils.php');

header("Content-Type: application/json");

$desiredFields = array(
    "id",
    "name",
    "street",
    "notes",
    "status",
    "zone",
    "order",
    "weekend",
    "driver",
    "email",
    "phone",
    "address"
);
$data = get_rows("tom_tmp_pickup",$_GET["dt"]=="true", $desiredFields);
print json_encode($data);
