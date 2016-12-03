$(document).ready(function() {
    $.getJSON("../php/db-get-users.php", function(users) {
        for (var i=0; i<users.length; i++) {
            var user = users[i];
            $("tbody").append('<tr><td>' + user.username + '</td><td><input type="checkbox"></td></tr>');
        }
    });

    $("#delete").click(function(){
        console.log("delete clicked")
    });

    $("#add").click(function(e){
        var username = $("#username").val();
        var password = $("#password").val();
        console.log(username,password);
        e.preventDefault();
    });
});