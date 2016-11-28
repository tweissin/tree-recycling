<?php
require('db-utils.php');

header("Content-Type: application/json");
$data = get_rows("tom_tmp_pickup");
print json_encode($data);
