
<?php

function navbar( $i)
{
    $i_on = "../images/checkout_bullet.gif";
    $i_off = "../images/pixel_silver.gif";
    $c_off= "off";
    $c_on = "on";

    $img = Array( $i_off, $i_off, $i_off, $i_off);
    $cls = Array( $c_off, $c_off, $c_off, $c_off);

    $img[ $i] = $i_on;
    $cls[ $i] = $c_on;

?>

<style>
.off {width: 1px;  height: 7px;}
.on { width: 11px; height: 11px;}
</style>

<table border="0" width="500px" cellspacing="0" cellpadding="0">
  <tr>
    <td width=20% >
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr height="10px">
	    <td width="50%" ></td>
	    <td><img src=<?php echo $img[ 0] ?> border="0" class="<?php echo $cls[ 0] ?>" alt=""></td>
	    <td width="50%"><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
	  </tr>
	</table>
    </td>
    <td width=20% >
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr height="10px">
	    <td width="50%" ><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
	    <td><img src=<?php echo $img[ 1] ?> border="0" class="<?php echo $cls[ 1] ?>" alt=""></td>
	    <td width="50%"><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
	  </tr>
	</table>
    </td>
    <td width=20% >
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr height="10px">
	    <td width="50%"><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
            <td><img src=<?php echo $img[ 2] ?> border="0" class="<?php echo $cls[ 2] ?>" alt=""></td>
	    <td width="50%"><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
	  </tr>
	</table>
    </td>
    <td width=20% >
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr height="10px">
	    <td width="50%"><img style="vertical-align: middle" src="../images/pixel_silver.gif" border="0" alt="" width="100%" height="1"></td>
	    <td><img src=<?php echo $img[ 3] ?> border="0" class="<?php echo $cls[ 3] ?>" alt=""></td>
	    <td width="50%"></td>
	  </tr>
	</table>
    </td>

    <td width=5%></td>
    <td width=15% align="left" class="checkoutBarFrom" colspan=4><a href="summary.php">Date<br>Management</a></td>
  </tr>
  <tr>
    <td align="center" width=20% class="checkoutBarFrom"><a href="index.php">Admin home</a></td>
    <td align="center" width=20% class="checkoutBarFrom"><a href="process_list.php">Review Requests</a></td>
    <td align="center" width=20% class="checkoutBarCurrent"><a href="set_routes.php">Assign Routes</a></td>
    <td align="center" width=20% class="checkoutBarTo"><a href="map_routes.php">Map Routes</a></td>
    <td colspan=2></td>
  </tr>

  <tr>

  </tr>
</table>

<?php

} 

?>

