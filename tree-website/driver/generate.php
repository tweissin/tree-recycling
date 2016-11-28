<?php
/**
 * Use this to generate a new password to go into access.txt.
 */
include('php/password.php');

if (isset($_POST['submit']) && !empty($_POST['text'])) {
    echo password_hash($_POST['text'],PASSWORD_BCRYPT);
}
?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
    <input type="text" name="text" placeholder="enter it">
    <button name="submit" type="submit">Submit</button>
</form>
