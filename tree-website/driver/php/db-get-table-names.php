<?php
/**
 * This is used to get a list of the pickup request table names.
 * A table name can be used to export a table to JSON, for instance.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

header("Content-Type: application/json");
$link = mysqli_connect(DB_HOST, DB_UNAME, DB_PSWD);
$result = mysqli_query($link, "show tables in " . DB_NAME . " like '%pickup%'");

$table_names = array();
while($cRow = mysqli_fetch_array($result))
{
    $table_names[] = $cRow[0];
}

mysqli_close($link);
print json_encode($table_names);
