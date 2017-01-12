<?php
session_start();
require_once('config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}
$filename = "export.csv";
header( 'Content-Type: text/csv' );
header( 'Content-Disposition: attachment;filename='.$filename);
$rows = get_rows("tom_tmp_pickup");
$out = fopen('php://output', 'w');
if (count($rows)>0) {
    $row = $rows[0];
    $print_comma = false;
    $arr = array();
    foreach ($row as $key => $value) {
        array_push($arr, $key);
    }
    fputcsv($out, $arr);
}
for ($i=0; $i<count($rows); $i++) {
    $row = $rows[$i];
    $arr = array();
    foreach ($row as $key => $value) {
        array_push($arr, $value);
    }
    fputcsv($out, $arr);
}
fclose($out);
?>

