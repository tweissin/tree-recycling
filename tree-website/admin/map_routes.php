<?php 
require( "header.php");

?>

<script type="text/javascript">

function choose_weekend()
{
    /* figure out which weekend we're doing */

    for( var i = 0; i < 3; i++)
    {
	if( document.test.map_num[i].checked)
	    break;
    }
    document.test.weekend.value = i;
    document.test.submit();
}

</script>

<title>T4 Map Tree Routes</title>
</head>

<body>

<?php

require( "navbar.php");

navbar(3);
?>

<h3>Map Tree Routes</h3>


<div id="header">
    <form name="test" action="map_date.php" enctype="application/x-www-form-urlencoded" method="post">
	<div id="weekend_dates">
	    <table class="medium-body">
	      <tr><td colspan=3><b>Weekend</b></td></tr>
	      <tr><td><input name="map_num" type="radio" value="0" onclick="choose_weekend()">1st</td>
		  <td><input name="map_num" type="radio" value="1" onclick="choose_weekend()">2nd</td>
		  <td><input name="map_num" type="radio" value="2" onclick="choose_weekend()">3rd</td></tr>
	    </table>
	</div>  <!-- weekend div -->
        <input name="weekend" id="weekend" type="hidden">
    <div id="instructions">
	<br>Choose which weekend to map.
    </div>

    </form>
</div>

</body>
</html>
