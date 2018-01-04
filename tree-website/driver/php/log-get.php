<?php
/**
 * This retrieves data from the log file which is updated
 * when people make tree pickup requests on the website.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

$log_filename = BASEDIR . "/../log.txt.bak";

/**
 * Make a backup in case the actual file is in use.
 */
function make_backup()
{
    global $log_filename;
    copy(BASEDIR . "/../log.txt", $log_filename);
}

function parse_log_into_array()
{
    global $log_filename;
    $records = array();
    $handle = @fopen($log_filename, "r");
    if ($handle) {
        while (($buffer = fgets($handle, 4096)) !== false) {
            $record = array();

            if (!strpos($buffer, "New Request")) {
                continue;
            }
            $pos = strpos($buffer, " success");
            if ($pos == 38) {
                continue;
            }

            $date = substr($buffer, 1, 10);
            $time = substr($buffer, 12, 8);

            array_push($record, $date);
            array_push($record, $time);

            $csv = substr($buffer, 35);

            foreach (explode("|", $csv) as $csv_item) {
                array_push($record, $csv_item);
            }
            // could be garbage records, only accept count==14
            if (count($record)==14) {
                array_push($records, $record);
            }

        }
        fclose($handle);
    }
    return $records;
}

function safe_json_encode($value, $options = 0, $depth = 512){
    $encoded = json_encode($value, $options, $depth);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = utf8ize($value);
            return safe_json_encode($clean, $options, $depth);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()

    }
}

function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}

header("Content-Type: application/json");
make_backup();
$data = parse_log_into_array();
$json = safe_json_encode($data);
print $json;