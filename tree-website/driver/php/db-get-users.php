<?php
/**
 * This is used to retrieve a list of allowed users.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

$users = get_rows("user");

$secure_users = array();
foreach ($users as $user) {
    unset($user["password"]);
    array_push($secure_users, $user);
}

header("Content-Type: application/json");
print json_encode($secure_users);
