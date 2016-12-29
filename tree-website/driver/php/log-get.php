<?php
/**
 * Created by IntelliJ IDEA.
 * User: tweissin
 * Date: 12/29/16
 * Time: 12:21 AM
 */
require_once('../config.php');

$log_filename = BASEDIR . "/../log.txt.bak";

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

header("Content-Type: application/json");
make_backup();
$data = parse_log_into_array();
print json_encode($data);