<?php

require_once('db-utils.php');

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

exec_prepared_statement("insert into user (username,password) values (?,?)", "ss", array($array["username"], $array["password"]));
