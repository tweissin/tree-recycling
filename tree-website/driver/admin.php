<?php
session_start();
require_once('config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!isset($_SESSION['valid']))
{
    header("Location: login.php");
    $_SESSION['loc']=$_SERVER['PHP_SELF'];
    exit;
}
?>
<html>
<head>
    <title>Driver Admin Utilities</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

</head>
<body>
<div class="container">
    <div id="status" style="display: none;"></div>

    <!-- Import/Export -->
    <h1>Tree Pickup Import/Export</h1>
    What do you want to do today?<p/>

    <button id="exportTable" class="btn btn-default" type="submit">Export DB records to JSON</button>
    <select id="tableNames"></select>
    <br/>
    <button id="importIntoTempDb" class="btn btn-default" type="submit">Import JSON into temp DB</button><br/>

    <div class="form-group">
        <label for="data">JSON records:</label>
        <textarea class="form-control" rows="50" id="jsonRecords"></textarea>
    </div>

    <h1>Download data as CSV</h1>
    <a href="download.php">Data as CSV</a>

    <h1>Tree Route Creator</h1>
    Click <a href="tree-route-creator.jnlp">here to launch the Tree Route Creator</a>

    <h1>Zone Mapping Worksheet</h1>
    You use the Zone Mapping worksheet to create routes around town.<br>
    <?php

    function get_spreadsheet_url()
    {
        // load the URL from the website
        return file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../../url.txt");
    }

    $url = get_spreadsheet_url();
    echo "<a href=\"$url\">Zone Mapping Worksheet</a>";

function get_emails()
{
    $date_0 = array();
    $date_1 = array();
    $date_2 = array();
    $emails = array(
        "date_0" => $date_0,
        "date_1" => $date_1,
        "date_2" => $date_2
    );

    foreach (get_rows("tmp_pickup") as $pickup)
    {
        $arr = $emails[$pickup["weekend"]];
        array_push($arr, $pickup["email"]);
        $emails[$pickup["weekend"]] = $arr;
    }
    return $emails;
}

function get_dates()
{
    $dates = array();
    foreach (get_rows("dates") as $date_row)
    {
        $dates["date_" . ($date_row["date_num"]-1)] = $date_row["cal_date"];
    }

    return $dates;
}

$dates = get_dates();
foreach (get_emails() as $weekend => $emails)
{
    echo "<h1>Emails ${dates[$weekend]}</h1>";
    echo "All the email addresses:<p>";
    echo "<span class='emails'>";
    foreach ($emails as $email)
    {
        echo "${email},";
    }
    echo "</span>";
}
?>

    <h1>Requests with comments</h1>
    The following are all requests that had comments.  This may be useful for feedback,
    like if money was removed but the tree was not taken, or if the tree was never picked up.<br>

    <table class="table table-striped" id="customerComments" border="1">
        <thead>
        <tr><td>Name</td><td>Email</td><td>Comment</td></tr>
        </thead>
        <tbody>

    <?php
    /**
     * Get requests with customer comments.
     */
    function print_customer_comments() {
        foreach (get_rows("tmp_pickup") as $pickup)
        {
            if (strlen($pickup["comments"])>0) {
                $email = $pickup["email"];
                echo "<tr><td>${pickup["name"]}</td><td><a href='mailto:$email'>$email</a></td><td>${pickup["comments"]}</td></tr>";
            }
        }
    }
    print_customer_comments();

    ?>
        </tbody>
    </table>

    <h1>Requests By Zone</h1>
    If too many or too few requests in a particular zone, you may want to rebalance.<br>
    <b>Note:</b> There is a hard limit of 23 requests in any one zone due to limitations with Google maps.

    <table class="table table-striped" id="requestsByZone" border="1">
        <thead>
        <tr><td>Weekend</td><td>Zone</td><td>Count</td></tr>
        </thead>
        <tbody></tbody>
    </table>

    <h1>Requests By Date</h1>
    <table class="table" id="requestsByDate" border="1"></table>


</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.16.6/lodash.min.js"></script>

<script src="js/admin.js"></script>
</body>
</html>

