<?php
/**
 * This has several various utilities related to the DB.
 */
require_once('../config.php');
require_once(BASEDIR . '/php/password.php');

function get_user_map()
{
    $users = get_rows("user");
    $res = Array();
    foreach($users as $u)
    {
        $user = $u["username"];
        $pass = $u["password"];
        $res[$user] = $pass;
    }
    return $res;
}

function is_password_valid( $pass_array, $user, $pass )
{
    if (!isset($pass_array[$user]))
        return False;
    $crypted = $pass_array[$user];
    return ($crypted == password_verify($pass,$crypted));
}

/**
 * This checks the user coming in from Basic auth.
 */
function check_basic_auth_user()
{
    echo $_SESSION['valid'];
    if (isset($_SESSION['valid']) && $_SESSION['valid']==true) {
        return true;
    }

    $user = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];
    $user_map = get_user_map();
    $valid = is_password_valid($user_map, $user, $pass);
    if (!$valid)
    {
        header('HTTP/1.0 403 Forbidden');
    }
    else
    {
        $_SESSION['valid'] = 'true';
    }
    return $valid;
}

function get_rows($table_name, $datatables=false, $desiredFields=null, $where_clause=null)
{
    $connid = mysql_connect( DB_HOST, DB_UNAME, DB_PSWD) or die("Could not connect : " . mysql_error());

    $selected = mysql_select_db( DB_NAME, $connid);
    if ( $selected == false)
    {
        print '<p>Database not selected</p>';
        print mysql_error();
        return array();
    }

    $sql = "select * from $table_name";
    if ($where_clause!=null) {
        $sql = "$sql where $where_clause";
    }
    $result = mysql_query($sql);
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
