<?php
/**
 * THis is used to delete a user from the DB.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'DELETE') {
    error_exit("this only handles DELETE requests", 5001);
}
$username = $_REQUEST["username"];

$rows = get_rows("user");
$exists = false;
for ($i=0; $i<count($rows); $i++) {
    $row = $rows[$i];
    if ($row["username"]==$username) {
        $exists = true;
        break;
    }
}
if (!$exists) {
    error_exit("user " . $username . " does not exist", 5000);
}

exec_prepared_statement("delete from user where username = ?", "s", array($username));
