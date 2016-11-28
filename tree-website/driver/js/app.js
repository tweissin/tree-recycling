$(document).ready(function() {
    var entryTemplate = Handlebars.compile($("#entry-template").html());
    var zoneTemplate = Handlebars.compile($("#zone-template").html());
    var dateTemplate = Handlebars.compile($("#date-template").html());
    var detailsTemplate = Handlebars.compile($("#details-template").html());
    const UI_ID=0, UI_NAME=1, UI_STREET=2, UI_NOTES=3, UI_STATUS=4, UI_ZONE=5, UI_ROUTE_ORDER=6, UI_WEEKEND=7, UI_DRIVER=8, UI_EMAIL=9, UI_PHONE=10, UI_ADDRESS=11;
    var dataTable;

    function getWeekArray(data) {
        var weekArray = [];
        for (var x=0; x<data.length; x++) {
            weekArray.push({
                id: data[x].id,
                name: data[x].name,
                street: data[x].street,
                address: data[x].address,
                comment: data[x].comments,
                driver: data[x].driver,
                status: data[x].status,
                route_order: data[x].route_order,
                zone: data[x].zone,
                email: data[x].email,
                phone: data[x].phone,
                weekend: data[x].weekend
            });
        } 
        return weekArray;
    }

    function getZones(data) {
        var zones = [];
        for (var x=0; x<data.length; x++) {
            zones.push({"id":data[x].zone});
        }
        zones.sort(function(lhs,rhs){
           return lhs["id"] - rhs["id"];
        });
        return _.uniqWith(zones, function(lhs,rhs) {
            return lhs.id==rhs.id;
        });
    }

    function filter(val, colNum) {
        var searchString;
        if (val=="----") {
            searchString = "";
        } else {
            searchString = '^' + val + "$";
        }

        $('#example').DataTable().column( colNum ).search(
            searchString,
            true, false, true
        ).order([ colNum, 'asc' ])
            .draw();
    }

    $("#selectZone").change(function() {
        filter($(this).val(), UI_ZONE);
    });

    $("#selectDay").change(function() {
        filter($(this).val(), UI_WEEKEND);
    });

    function getDataForId(id) {
        var allData = $('#example').DataTable().data();
        for (var x=0; x<allData.length; x++) {
            if (allData[x][UI_ID]==id) {
                var data = allData[x];
                var dataMap = {
                    id: data[UI_ID],
                    name: data[UI_NAME],
                    street: data[UI_STREET],
                    address: data[UI_ADDRESS],
                    encodedAddress: encodeURI(data[UI_ADDRESS]),
                    notes: data[UI_NOTES],
                    driver: data[UI_DRIVER],
                    email: data[UI_EMAIL],
                    phone: data[UI_PHONE],
                    status: data[UI_STATUS]
                };
                return dataMap;
            }
        }
        return null;
    }

    function setupEvents() {
        $("table tr").click(function() {
            var id = $(this).attr('id');
            var data = getDataForId(id);
            var detailsHtml = detailsTemplate(data);
            var driver = $("#driver");
            var saveStatus = $("#saveStatus");
            $('#customerModal .modal-title').html(data.name);
            $('#customerModal .modal-body').html(detailsHtml);

            saveStatus.hide();
            $(".pickup-status-btn").click(function(data) {
                var choice = $(data.target).attr("value");

                $("#confirm").text(choice);
                $("#confirm-panel").css("visibility","visible");
                $("#saveStatus").fadeOut();
            });
            $("#confirm").click(function() {
                console.log("updating " + id + " with", $("#confirm").text());
                $.ajax({
                    type: 'POST',
                    url: 'php/db-update-pickup.php',
                    data: JSON.stringify({
                        "id":id,
                        "status":$("#confirm").text(),
                        "driver":driver.val()
                    }),
                    success: function() {
                        var saveStatus = $("#saveStatus");
                        saveStatus.text("Saved!");
                        saveStatus.fadeIn();
                        dataTable.draw();
                    },
                    error: function(xhr, status, error) {
                        var saveStatus = $("#saveStatus");
                        saveStatus.text("error: " + xhr.responseJSON.message);
                        saveStatus.fadeIn();
                        console.log("status:", status, " error:", error);
                    }
                });
            });

            $('#customerModal').modal('show');
        });
    }

    function refresh() {
        $.getJSON("php/db-get-pickups.php", function(data) {
            console.log('read pickups from db');
        
            var weekArray = getWeekArray(data);
            var customers = { customer: weekArray };
            var customersHtml = entryTemplate(customers);
            $("tbody").html(customersHtml);

            var zones = { zone: getZones(data) };
            var zonesHtml = zoneTemplate(zones);
            $("#selectZone").append(zonesHtml);

            dataTable = $('#example').DataTable( {
                "order": [[ UI_ZONE, 'asc'], [UI_ROUTE_ORDER, 'asc']],
                    "columnDefs": [
                        {
                            "targets": [ UI_ID, UI_EMAIL, UI_PHONE, UI_ADDRESS ], // hide
                            "visible": false
                        }
                    ],
                    "fnDrawCallback": function( oSettings ) {
                          setupEvents();
                    }
            });

        });
        var driver = $("#driver");
        $.getJSON("php/session.php", function(data){
            if (data.driver) {
                driver.val(data.driver);
            }
        });

        driver.blur(function() {
            var name = driver.val();
            $.ajax({
                type: 'POST',
                url: 'php/session.php',
                data: JSON.stringify({"driver":name}),
                success: function(msg) {
                    console.log("saved " + name,msg);
                },
                error: function(xhr, status, error) {
                    console.log("status:", status, " error:", error);
                }
            });
        });
        $.getJSON("php/db-get-dates.php", function(data) {
            console.log('read dates from db');

            var dates = {date: data};
            var datesHtml = dateTemplate(dates);
            $("#selectDay").append(datesHtml);
        });
    }
    refresh();
} );