<?php
/**
 * This is used to get a list of dates from the DB.
 * These are used to display to the user.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

header("Content-Type: application/json");
$data = get_rows("dates");
print json_encode($data);
