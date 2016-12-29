<html>
<head>
    <title>Driver Admin</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

</head>
<body>
<div class="container">

    <!-- User List -->
    <h1>User list</h1>
    <table id="userTable" class="table table-striped">
        <thead>
        <tr>
            <td>Username</td>
            <td>Select</td>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <button id="delete">Delete checked</button>

    <!-- Add User -->
    <h1>Add user</h1>
    <form>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" placeholder="Username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Password">
        </div>
        <button id="add" type="submit" class="btn btn-default">Add</button>
    </form>
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

    <h1>Tree Route Creator</h1>
    Click <a href="tree-route-creator.jnlp">here to launch the Tree Route Creator</a>


    <?php
require_once('../config.php');
require_once(BASEDIR . '/php/db-utils.php');

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
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.16.6/lodash.min.js"></script>

<script src="../js/admin.js"></script>
</body>
</html>

