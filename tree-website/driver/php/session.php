<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $_SESSION["data"] = file_get_contents('php://input');
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    if (!$_SESSION["data"])
    {
        print "{}";
    }
    else
    {
        print $_SESSION["data"];
    }
}