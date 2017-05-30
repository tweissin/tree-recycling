<?php
/**
 * This is used to add a user into the DB.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);

$rows = get_rows("user");
for ($i=0; $i<count($rows); $i++) {
    $row = $rows[$i];
    if ($row["username"]==$array["username"]) {
        error_exit("user already exists",4096);
        return;
    }
}

$pwd = $passwordStrategy->make_password($array["password"]);
exec_prepared_statement("insert into user (username,password) values (?,?)", "ss", array($array["username"], $pwd));
