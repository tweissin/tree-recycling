<?php
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

header("Content-Type: application/json");
$rows = get_rows("information_schema.tables", false, null, "table_schema='trees' and `TABLE_TYPE`='BASE TABLE' and table_name like '%pickup%'");
$table_names = array();
for ($i=0; $i<count($rows); $i++) {
    $row = $rows[$i];
    array_push($table_names, $row["TABLE_NAME"]);
}
print json_encode($table_names);
