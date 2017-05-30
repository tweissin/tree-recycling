<?php
session_start();
require_once('config.php');
require_once(BASEDIR . '/php/db-utils.php');

if (!$passwordStrategy->is_session_valid())
{
    header("Location: login.php");
    $_SESSION['loc']=$_SERVER['PHP_SELF'];
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Tree Pickup App</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
 
    <!-- Data tables -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css" crossorigin="anonymous">

  </head>
  <body>
    <div class="container">
      <form>
        <div class="form-group">
          <label for="selectDriver">Who is the driver?</label>
          <input type="input" class="form-control" id="driver" placeholder="Driver name">
        </div>
        <div class="form-group">
          <label for="selectDay">Select Weekend</label>
          <select name="day" id="selectDay" class="form-control">
            <option value="----">--Select--</option>
          </select>
        </div>
        <div class="form-group">
          <label for="selectZone">Select Zone</label>
          <select name="zone" id="selectZone" class="form-control">
            <option value="----">--Select--</option>
          </select>
        </div>

        <table id="example" class="display" cellspacing="0" width="100%">
          <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Street</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Zone</th>
            <th>Order</th>
            <th>Weekend</th>
            <th>Driver</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
          </tr>
          </thead>
          <tfoot>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Street</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Zone</th>
            <th>Order</th>
            <th>Weekend</th>
            <th>Driver</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
          </tr>
          </tfoot>
        </table>
      </form>
    </div>

    <script id="entry-template" type="text/x-handlebars-template">
      {{#each customer}}
        <tr id="{{id}}">
          <td class="id">{{id}}</td>
          <td class="name">{{name}}</td>
          <td class="street">{{street}}</td>
          <td class="comment">{{comment}}</td>
          <td class="status">{{status}}</td>
          <td class="zone">{{zone}}</td>
          <td class="route_order">{{route_order}}</td>
          <td class="weekend">{{weekend}}</td>
          <td class="driver">{{driver}}</td>
          <td class="email">{{email}}</td>
          <td class="phone">{{phone}}</td>
          <td class="address">{{address}}</td>
        </tr>
      {{/each}}
    </script>

    <script id="zone-template" type="text/x-handlebars-template">
      {{#each zone}}
        <option value="{{id}}">{{id}}</option>
      {{/each}}
    </script>

    <script id="date-template" type="text/x-handlebars-template">
      {{#each date}}
      <option value="{{cal_date}}">{{cal_date}}</option>
      {{/each}}
    </script>

    <script id="details-template" type="text/x-handlebars-template">
      <a href="https://maps.google.com/maps/place/{{encodedAddress}}" target="_blank">{{address}}</a><br>
        {{email}} {{phone}}<br>
      Notes: {{comments}}<br>
      <button value="pickedUp" type="button" class="btn btn-default pickup-status-btn">Pick Up Complete</button><br>
      <button value="noTree" type="button" class="btn btn-default pickup-status-btn">No Tree</button><br>
      <button value="pickedUpNoMoney" type="button" class="btn btn-default pickup-status-btn">Pick Up No Money</button><p/>

      <div id="confirm-panel" style="visibility: hidden" class="panel panel-default">
        <div class="panel-body">
          Confirm
          <button id="confirm" type="button" class="btn btn-default"></button><p/>
        </div>
      </div>

      <div id="saveStatus" style="display: none;"></div>
    </script>

    <div id="customerModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Customer Data</h4>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          </div>
        </div>

      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.16.6/lodash.min.js"></script>

    <script src="js/app.js"></script>
  </body>
</html>
