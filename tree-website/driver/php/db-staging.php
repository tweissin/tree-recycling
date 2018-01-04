<?php
/**
 * This is used to export pickup request data to or from CSV.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

function export_to_csv($table_name) {
    $data = get_rows($table_name);

    foreach ($data as &$row) {
        $row["update"] = false;
    }
    header('Content-Type: application/json; charset=UTF-8');
    print json_encode($data);
}

/**
 * Check if row exists.
 * @param $current_rows all current rows
 * @param $data_for_import the proposed data for import
 * @return bool true if the row exists
 */
function does_row_exist($current_rows, $data_for_import) {
    for ($i=0; $i<count($current_rows); $i++) {
        $current_row = $current_rows[$i];
        if ($current_row["id"] == $data_for_import["id"]) {
            return true;
        }
    }
    return false;
}

function update_row($link, $schema, $table_name, $data_for_import, &$records_updated) {
    $stmt = "UPDATE $table_name SET ";
    $first = true;

    foreach ($schema as $field => $type) {
        if (array_key_exists($field, $data_for_import)) {
            if ($field=="id") {
                continue;
            }

            if (!$first) {
                $stmt = "$stmt, ";
            }

            $first = false;
            $stmt = "${stmt}${field}=";

            $val = $data_for_import[$field];
            if ($type=="text") {
                $val = mysql_real_escape_string($val);
                $stmt = "${stmt}'${val}'";
            } else {
                if (strlen($val)==0) {
                    $val = 0;
                }
                $stmt = "${stmt}${val}";
            }
        }
    }

    $stmt = "${stmt} WHERE id=${data_for_import['id']}";

    $success = mysqli_query($link, $stmt);
    if (!$success) {
        $last_err = error_get_last();
        mysqli_query($link,"ROLLBACK");
        print_r ($last_err);
        error_exit("failed to update: " . $last_err["message"],1024);
        return array();
    }
    $records_updated++;
}

function import_from_csv($table_name, $data) {
    $link = mysqli_connect(DB_HOST, DB_UNAME, DB_PSWD);
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    $selected = mysqli_select_db($link, DB_NAME);
    if ( $selected == false) {
        error_exit("can't select that DB", 1234);
    }

    mysqli_query($link, "BEGIN");

    $schema = array(
        "id"=>"number",
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

    $current_rows = get_rows($table_name);
    $records_imported = 0;
    $records_updated = 0;

    $log = "";
    for ($i=0; $i<count($data); $i++) {
        $data_for_import = $data[$i];
        if (does_row_exist($current_rows, $data_for_import)) {
            $log = "${log} ${$data_for_import} does_row_exist is true";
            if ($data_for_import["update"]==true) {
                $log = "${log} update=true";
                update_row($link, $schema, $table_name, $data_for_import, $records_updated);
            }

            continue;
        }

        $stmt = "INSERT INTO $table_name (";
        $first = true;
        foreach ($schema as $field => $type) {
            if (array_key_exists($field, $data_for_import)) {
                if (!$first) {
                    $stmt = "$stmt, ";
                }
                $first = false;
                $stmt = "${stmt}${field}";
            }
        }
        $stmt = "${stmt}) VALUES (";
        $first = true;
        foreach ($schema as $field => $type) {
            if (array_key_exists($field, $data_for_import)) {
                if (!$first) {
                    $stmt = "$stmt, ";
                }
                $first = false;
                $val = $data_for_import[$field];
                if ($type=="text") {
                    $val = mysql_real_escape_string($val);
                    $stmt = "${stmt} '${val}'";
                } else {
                    if (strlen($val)==0) {
                        $val = 0;
                    }
                    $stmt = "${stmt} ${val}";
                }
            }
        }
        $stmt = "${stmt})";

        $success = mysqli_query($link, $stmt);
        if (!$success) {
            $last_err = error_get_last();
            mysqli_query($link,"ROLLBACK");
            print_r ($last_err);
            error_exit("failed to insert: " . $last_err["message"],1024);
            return array();
        }
        $records_imported++;
    }
    mysqli_query($link,"COMMIT");
    return array(
        "records_imported" => $records_imported,
        "records_updated" => $records_updated,
        "total_rows" => count($data)
    //, "log" => $log
    );
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);

if (isset($array["op"])) {
    if ($array["op"]=="export") {
        export_to_csv($array["table"]);
    }
    if ($array["op"]=="import") {
        header('Content-Type: application/json; charset=UTF-8');
        $response = import_from_csv($array["table"], $array["data"]);
        print json_encode($response);
    }
}