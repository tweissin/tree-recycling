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
                $("#exportedRecords").text(JSON.stringify(JSON.parse(data),null,4));
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
});