<?php
require_once('db-utils.php');
$users = get_rows("user");
header("Content-Type: application/json");
print json_encode($users);
