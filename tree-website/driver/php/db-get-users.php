<?php
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');
$users = get_rows("user");

$secure_users = array();
foreach ($users as $user) {
    unset($user["password"]);
    array_push($secure_users, $user);
}

header("Content-Type: application/json");
print json_encode($secure_users);
