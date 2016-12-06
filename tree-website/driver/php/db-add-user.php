<?php

require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');
require_once(BASEDIR . '/php/password.php');

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

$pwd = password_hash($array["password"],PASSWORD_BCRYPT);
exec_prepared_statement("insert into user (username,password) values (?,?)", "ss", array($array["username"], $pwd));
