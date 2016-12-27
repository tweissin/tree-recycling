$(document).ready(function() {

    $("#delete").click(function(){
        setStatus("");
        $('#userTable').find('input[type="checkbox"]:checked').each(function () {
            var username = $(this).parent().parent().find("td").first().text();
            $.ajax({
                url: '../php/db-delete-user.php?username=' + username,
                type: 'DELETE',
                success: function(result) {
                    var msg = $("#status").text();
                    msg += "(deleted " + username + ")";
                    setStatus(msg);
                    refreshUserList();
                },
                error: function() {
                    var msg = $("#status").text();
                    msg += "(FAILED to delete " + username + ")";
                    setStatus(msg);
                }
            });
        });
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

    function exportIt(tableName) {
        setStatus("");
        $.ajax({
            type: 'POST',
            url: '../php/db-staging.php',
            data: JSON.stringify({
                op: "export",
                table: tableName
            }),
            success: function(data) {
                setStatus("successfully exported data");
                $("#jsonRecords").text(JSON.stringify(data,null,4));
            },
            error: function(xhr, status, error) {
                console.log("error, status:", status, " error:", error);
            }
        });
    }
    function importIt(tableName) {
        setStatus("");
        var data = $("#jsonRecords").text();
        var jsonData = JSON.parse(data);
        $.ajax({
            type: 'POST',
            url: '../php/db-staging.php',
            data: JSON.stringify({
                op: "import",
                table: tableName,
                data: jsonData
            }),
            success: function(data) {
                setStatus(JSON.stringify(data,null,4));
            },
            error: function(xhr, status, error) {
                setStatus("error, status:" + status + " error:" + error);
            }
        });
    }

    $.getJSON("../php/db-get-table-names.php", function(tables) {
        for (var i = 0; i < tables.length; i++) {
            var table = tables[i];
            $('<option value="' + table + '">' + table + '</option>').appendTo("#tableNames");
        }
    });

    $("#exportTable").click(function() {
        var table = $('#tableNames').find(":selected").text();
        exportIt(table);
    });

    $("#importIntoTempDb").click(function() {
        importIt("tom_tmp_pickup");
    });

    refreshUserList();
});