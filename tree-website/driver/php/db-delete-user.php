<?php

require_once('db-utils.php');

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
