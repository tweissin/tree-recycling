<?php
require('db-utils.php');

header("Content-Type: application/json");
$data = get_rows("dates");
print json_encode($data);
