<?php
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

header("Content-Type: application/json");
$data = get_rows("dates");
print json_encode($data);
