<?php

require('db-utils.php');

function export_to_csv($table_name) {
    $data = get_rows($table_name);
    header('Content-Type: application/json; charset=UTF-8');
    print json_encode($data);
}

function import_from_csv($table_name, $data) {
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());
    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false) {
        error_exit("can't select that DB", 1234);
    }

    mysql_query("BEGIN");

    $schema = array(
        "temp"=>"text",
        "route"=>"text",
        "lat"=>"text",
        "lng"=>"text",
        "weekend"=>"text",
        "name"=>"text",
        "street"=>"text",
        "email"=>"text",
        "confirm"=>"text",
        "phone"=>"text",
        "small"=>"number",
        "large"=>"number",
        "source"=>"text",
        "comments"=>"text",
        "status"=>"text",
        "driver"=>"text",
        "address"=>"text",
        "zone"=>"number",
        "route_order"=>"number",
        );

    foreach ($data as $value) {

        $stmt = "INSERT INTO $table_name (";
        $first = true;
        foreach ($schema as $field => $type) {
            if (!$first) {
                $stmt = "$stmt, ";
            }
            $first = false;
            $stmt = "${stmt}${field}";
        }
        $stmt = "${stmt}) VALUES (";
        $first = true;
        foreach ($schema as $field => $type) {
            if (!$first) {
                $stmt = "$stmt, ";
            }
            $first = false;
            $val = $value[$field];
            if ($type=="text") {
                $stmt = "${stmt} '${val}'";
            } else {
                if (strlen($val)==0) {
                    $val = 0;
                }
                $stmt = "${stmt} ${val}";
            }
        }
        $stmt = "${stmt})";

        $success = mysql_query($stmt);
        if (!$success) {
            $last_err = error_get_last();
            mysql_query("ROLLBACK");
            print_r ($last_err);
            error_exit("failed to insert: " . $last_err["message"],1024);
            return;
        }
    }
    mysql_query("COMMIT");
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);

if (isset($array["op"])) {
    if ($array["op"]=="export") {
        export_to_csv($array["table"]);
    }
    if ($array["op"]=="import") {
        import_from_csv($array["table"], $array["data"]);
    }
}