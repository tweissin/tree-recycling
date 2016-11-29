<?php

require('db-utils.php');

function export_to_csv($table_name) {
    $data = get_rows($table_name);
    print json_encode($data);
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);

if (isset($array["op"]) && $array["op"]=="export") {
    export_to_csv($array["table"]);
}
