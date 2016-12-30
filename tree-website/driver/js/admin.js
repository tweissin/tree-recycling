$(document).ready(function() {

    function setStatus(msg) {
        var status = $("#status");
        status.text(msg);
        status.fadeIn();
    }

    function exportIt(tableName) {
        setStatus("");
        $.ajax({
            type: 'POST',
            url: 'php/db-staging.php',
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
            url: 'php/db-staging.php',
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

    $.getJSON("php/db-get-table-names.php", function(tables) {
        // put tmp_pickup first
        var tmp_pickup = 'tmp_pickup';
        $('<option value="' + tmp_pickup + '">' + tmp_pickup + '</option>').appendTo("#tableNames");

        for (var i = 0; i < tables.length; i++) {
            var table = tables[i];
            if (table==tmp_pickup) continue;
            $('<option value="' + table + '">' + table + '</option>').appendTo("#tableNames");
        }
    });

    $.getJSON("php/log-get.php", function(rows) {
        var table = $("#requestsByDate");
        var requestsByDate = rows.reduce(function (allDates, row) {
            var date = row[0];
            if (date in allDates) {
                allDates[date]++;
            } else {
                allDates[date] = 1;
            }
            return allDates;
        }, {});
        for (var key in requestsByDate) {
            var val = requestsByDate[key];
            $('<tr><td>' + key + '</td><td>' + val + '</td></tr>').appendTo(table);
        }
    });

    $("#exportTable").click(function() {
        var table = $('#tableNames').find(":selected").text();
        exportIt(table);
    });

    $("#importIntoTempDb").click(function() {
        importIt("tom_tmp_pickup");
    });
});