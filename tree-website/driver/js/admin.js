$(document).ready(function() {

    $("#delete").click(function(){
        console.log("delete clicked")
    });

    function setStatus(msg) {
        var status = $("#status");
        status.text(msg);
        status.fadeIn();
    }

    function refreshUserList() {
        $("tbody").empty();
        $.getJSON("../php/db-get-users.php", function(users) {
            for (var i=0; i<users.length; i++) {
                var user = users[i];
                $("tbody").append('<tr><td>' + user.username + '</td><td><input type="checkbox"></td></tr>');
            }
        });
    }

    $("#add").click(function(e){
        $("#status").fadeOut();
        var username = $("#username").val();
        var password = $("#password").val();
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: '../php/db-add-user.php',
            data: JSON.stringify({
                "username":username,
                "password":password
            }),
            success: function() {
                setStatus("added");
                refreshUserList();
            },
            error: function(xhr, status, error) {
                setStatus("error: " + xhr.responseJSON.message);
            }
        });
    });

    refreshUserList();
});