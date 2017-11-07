<?php
/**
 * This is used to add a user into the DB.
 */
session_start();
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!check_basic_auth_user())
{
    exit();
}

$str_json = file_get_contents('php://input');
$array = json_decode(json_encode(json_decode($str_json)), true);

$to_array = $array['to'];
$subject = $array['subject'];
$message = $array['message'];

if (is_null($to_array)) { error_exit("specify 'to' address"); }
if (is_null($subject)) { error_exit("specify subject"); }
if (is_null($message)) { error_exit("specify message"); }

$headers = 'From: tree-request@troop4hopkinton.org' . "\r\n" . 'Reply-To: tree-request@troop4hopkinton.org' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

$to_addresses = "";
foreach ($to_array as &$to) {
    if (strlen($to_addresses)>0) {
        $to_addresses .= ",";
    }
    $to_addresses .= $to;
}

$status = mail($to_addresses, $subject, $message, $headers);

