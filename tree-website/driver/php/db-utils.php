<?php
require_once('../config.php');

function get_rows($table_name, $datatables=false, $desiredFields=null)
{
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());

    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
        print '<p>Database not selected</p>';
        print mysql_error();
        return array();
    }

    $result = mysql_query( "select * from " . $table_name);
    $rows = array();
    while ($row = mysql_fetch_assoc($result)) {
        if ($desiredFields) {
            $desiredRow = array();
            foreach ($desiredFields as $desiredField) {
                // TODO: throw an error if field doesn't exist in table
                $desiredRow[$desiredField] = $row[$desiredField];
            }
            array_push($rows, $desiredRow);
        } else {
            array_push($rows, $row);
        }
    }
    if ($datatables) {
        return array("data" => $rows);
    }
    return $rows;
}

function exec_sql($stmt) {
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());
    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
        $last_err = error_get_last();
        error_exit("Database not selected: " . $last_err["message"],2048);
        return;
    }
    $success = mysql_query($stmt);
    if (!$success) {
        $last_err = error_get_last();
        error_exit("failed to exec sql: " . $last_err["message"],3072);
        return;
    }
}

function exec_prepared_statement($sql, $types, $args) {
    $link = mysqli_connect(DB_HOST, DB_UNAME, DB_PSWD);
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    mysqli_select_db($link, DB_NAME);

    if ($stmt = mysqli_prepare($link, $sql)) {
        switch (count($args)) {
            case 1:
                mysqli_stmt_bind_param($stmt, $types, $args[0]);
                break;
            case 2:
                mysqli_stmt_bind_param($stmt, $types, $args[0], $args[1]);
                break;
            case 3:
                mysqli_stmt_bind_param($stmt, $types, $args[0], $args[1], $args[2]);
                break;
            default:
                error_exit("can't handle this number of args: " . count($args), 5001);
                break;
        }
        $success = mysqli_stmt_execute($stmt);

        if (!$success) {
            header('HTTP/1.1 500 Internal Server Booboo');
            header('Content-Type: application/json; charset=UTF-8');
            print json_encode(array(
                    "error_msg" => mysqli_stmt_error($stmt),
                    "error_id" => mysqli_stmt_errno($stmt))
            );
            exit();
        }

        mysqli_stmt_close($stmt);
    } else {
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        print json_encode(array(
                "error_msg" => mysqli_error($link),
                "error_id" => mysqli_errno($link))
        );
        exit();
    }
    mysqli_close($link);
}

function error_exit($message, $code)
{
    header('HTTP/1.1 500 Internal Server Booboo');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => $message, 'code' => $code)));
}
