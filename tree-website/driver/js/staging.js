$(document).ready(function() {
    function exportIt(tableName) {
        $.ajax({
            type: 'POST',
            url: 'php/db-staging.php',
            data: JSON.stringify({
                op: "export",
                table: tableName
            }),
            success: function(data) {
                console.log("success");
                $("#jsonRecords").text(JSON.stringify(data,null,4));
            },
            error: function(xhr, status, error) {
                console.log("error, status:", status, " error:", error);
            }
        });
    }
    function importIt(tableName) {
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
                console.log("success");
            },
            error: function(xhr, status, error) {
                console.log("error, status:", status, " error:", error);
            }
        });
    }

    $("#exportProduction").click(function() {
        exportIt("tmp_pickup");
    });

    $("#exportTempDb").click(function() {
        exportIt("tom_tmp_pickup");
    });

    $("#importIntoProduction").click(function() {
        //importIt("tmp_pickup");
        console.log("not gonna do it");
    });

    $("#importIntoTempDb").click(function() {
        importIt("tom_tmp_pickup");
    });
});