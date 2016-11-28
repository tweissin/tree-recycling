<?php
session_start();
include('php/password.php');

define("HTPASSWDFILE", "access.txt");

function load_htpasswd()
{
    if ( !file_exists(HTPASSWDFILE))
        return Array();

    $res = Array();
    foreach(file(HTPASSWDFILE) as $l)
    {
        $array = explode(':',$l);
        $user = $array[0];
        $pass = chop($array[1]);
        $res[$user] = $pass;
    }
    return $res;
}

function test_htpasswd( $pass_array, $user, $pass )
{
    if (!isset($pass_array[$user]))
        return False;
    $crypted = $pass_array[$user];
    return ($crypted == password_verify($pass,$crypted));
}

$pass_array = load_htpasswd();
if (isset($_POST['login']) && !empty($_POST['username'])
    && !empty($_POST['password'])) {

    if (test_htpasswd($pass_array, $_POST['username'], $_POST['password'])) {
        $_SESSION['valid'] = 'true';
        $_SESSION['timeout'] = time();
        $_SESSION['username'] = $_POST['username'];

        header("Location: index.php");
        exit;
    }else {
        echo 'Wrong username or password';
    }
}
?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

</head>
<body>
<div class="container">
<form class = "form-signin" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" name="username" placeholder="Username">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password" placeholder="Password">
    </div>
    <button name="login" type="submit" class="btn btn-default">Submit</button>
</form>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>

