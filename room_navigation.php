<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Input Page</title>
    <link rel="shortcut icon" type="image/png" href="admin/assets/images/logos/logo_sec.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAm4XE_BJt3LlMcvJi1erXZcY7Ln8xA-qg&callback=initAllLocationsMap"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>

    <!-- Load Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin/assets/css/styles.min.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }

        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            max-height: 200px; /* Set a fixed max height for the autocomplete list */
            overflow-y: auto; /* Make the list scrollable */
            z-index: 99;
            width: 300px;
            text-align: left;
            background-color: #fff;
        }
        .autocomplete-items div {
        padding: 10px;
        cursor: pointer;
        background-color: #fff;
        border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
        /*when hovering an item:*/
        background-color: #e9e9e9;
        }
        .autocomplete-active {
        /*when navigating through the items using the arrow keys:*/
        background-color: DodgerBlue !important;
        color: #ffffff;
        }
        #bfrname{
            font-weight: bold;
        }
    </style>

 
    <?php
    include_once 'admin/db_con/db.php';
    // SQL query to retrieve autocomplete data
    $sql = "SELECT r.room_id, f.floor_id, b.label building_label, f.name floor_name, r.room_name FROM tbl_rooms r LEFT JOIN tbl_floors f ON r.floor_id = f.floor_id LEFT JOIN tbl_building b ON f.building_id = b.building_id;";
    
    $result = $conn->query($sql);
    
    $autocompleteData = array();
    
    if ($result->num_rows > 0) {
        // Fetch associative array of rows
        while ($row = $result->fetch_assoc()) {
            // Format the data as needed for autocomplete
            $autocompleteData[] = $row['floor_id'] . ', ' . $row['room_id'] . ', ' . $row['building_label'] . ', ' . $row['floor_name'] . ', ' . $row['room_name'];
        }
    }
    
    // Close database connection
    $conn->close();

    ?>
       
</head>
<body>
    <div class="navigation-page-container">
        <div class="row justify-content-start mb-3">
            <div class="col-auto">
                <a class="btn btn-none float-left" href="navigation_input_page.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l4 4" /><path d="M5 12l4 -4" /></svg> Specific Location
                </a>
            </div>
        </div>
        <div class="form-con">
            <h3><b>Buildings Navigation Input</b></h3>
            <div class="navigation-page">
                <h5>Search a Location:</h5>
                <div class="form-inline justify-content-center">
                    <form autocomplete="off" onsubmit="event.preventDefault()">
                        <input type="text" id="searchInput" style="width: 300px;" class="form-control mr-2">
                        <button data-toggle="modal" onclick="getRoomID()" data-target="#searchviewFloorModal" class="btn btn-primary mr-2"><i class="ti ti-location"></i></button>
                    </form>
                </div>
                <p id="error-message" style="color: red;"></p>
            </div>
            <div id="allLocationsMap" style="height: 600px; width: 100%;"></div>
        </div>
    </div>
    <!-- View All floor -->
    <div class="modal fade" id="viewFloorModal" tabindex="-1" role="dialog" aria-labelledby="viewFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFloorModalLabel">View Floor</h5>
                </div>
                <div class="modal-body">
                    <label class="mb-1" for="floorDropdown">Select a Floor:</label>
                    <select id="floorDropdown" class="form-control mb-4">
                        <!-- Floor options will be populated here dynamically -->
                    </select>
                    <div id="floorImageContainer" style="width: 100%; height: 400px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- View searched floor -->
    <div class="modal fade" id="searchviewFloorModal" tabindex="-1" role="dialog" aria-labelledby="searchviewFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Result</h5>
                </div>
                <div class="modal-body">
                <div class="row justify-content-center mb-3">
                <h3 id="bfrname" class="text-center"></h3> <!-- Added 'text-center' class -->
                </div>
                <div id="searchedfloorImageContainer" style="width: 100%; height: 400px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
    
    <?php include_once 'room_nav_function.php'; ?>
    <script>
        var roomID = 0;
        var floorID = 0;
        var name = "";
    // Function to initialize autocomplete
        function autocomplete(inp, arr) {
            var currentFocus;

            inp.addEventListener("input", function(e) {
                var val = this.value;
                closeAllLists();
                if (!val) { return false;}
                currentFocus = -1;
                var a = document.createElement("DIV");
                a.setAttribute("id", this.id + "autocomplete-list");
                a.setAttribute("class", "autocomplete-items");
                this.parentNode.appendChild(a);
                for (var i = 1; i < arr.length; i++) { // Start from index 1
                    var autoCompleteItem = arr[i];
                    if (autoCompleteItem.toUpperCase().includes(val.toUpperCase())) {
                        var b = document.createElement("DIV");
                        var startIndex = autoCompleteItem.toUpperCase().indexOf(val.toUpperCase());
                        var matchingText = autoCompleteItem.substr(startIndex, val.length);
                        var restOfText = autoCompleteItem.substr(startIndex + val.length);
                        b.innerHTML = autoCompleteItem.substr(0, startIndex) + "<strong>" + matchingText + "</strong>" + restOfText;
                        b.addEventListener("click", function(e) {
                            var selectedValue = this.innerText;
                            var floor_Id = selectedValue.split(',')[0];
                            var room_Id = selectedValue.split(',')[1];
                            var building = selectedValue.split(',')[2];
                            var floor = selectedValue.split(',')[3];
                            var room = selectedValue.split(',')[4];
                            console.log("Selected Room ID:", room_Id);
                            console.log("Selected floor ID:", floor_Id);
                            roomID = room_Id;
                            floorID = floor_Id;
                            name = building +', '+ floor +', '+ room;
                            inp.value = selectedValue;
                            closeAllLists();
                        });
                        a.appendChild(b);
                    }
                }
            });

            inp.addEventListener("keydown", function(e) {
                var x = document.getElementById(this.id + "autocomplete-list");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode == 40) {
                    currentFocus++;
                    addActive(x);
                } else if (e.keyCode == 38) { 
                    currentFocus--;
                    addActive(x);
                } else if (e.keyCode == 13) {
                    e.preventDefault();
                    if (currentFocus > -1) {
                        if (x) x[currentFocus].click();
                    }
                }
            });

            function addActive(x) {
                if (!x) return false;
                removeActive(x);
                if (currentFocus >= x.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (x.length - 1);
                x[currentFocus].classList.add("autocomplete-active");
            }

            function removeActive(x) {
                for (var i = 0; i < x.length; i++) {
                    x[i].classList.remove("autocomplete-active");
                }
            }

            function closeAllLists(elmnt) {
                var x = document.getElementsByClassName("autocomplete-items");
                for (var i = 0; i < x.length; i++) {
                    if (elmnt != x[i] && elmnt != inp) {
                        x[i].parentNode.removeChild(x[i]);
                    }
                }
            }

            document.addEventListener("click", function (e) {
                closeAllLists(e.target);
            });
        }

        // PHP variable containing the autocomplete data from the database
        var autocompleteData = <?php echo json_encode($autocompleteData); ?>;

        // Call the autocomplete function with the input element and the autocomplete data
        autocomplete(document.getElementById("searchInput"), autocompleteData);

        function getRoomID() {
            if (roomID === 0) {
                document.getElementById('error-message').innerText = "No match found. Please select a valid location.";
            } else {
                document.getElementById('error-message').innerText = ""; // Clear error message if roomID is selected
                console.log("Room ID:", roomID);
                setTimeout(function() {
                    displaySearchFloorImage(floorID);
                    setTimeout(function() {
                        displaySavedRooms(floorID, roomID);
                    }, 500);
                }, 500);
              
                $('#searchviewFloorModal').modal('hide'); // Dismiss modal if roomID is selected
                document.getElementById('bfrname').innerText = name; // Set the modal title with the selected room name
            }
        }

        

        var searchfloorMap;
        function initializeViewSearchFloorMap(imageUrl) {
            // Check if floorMap already exists; if so, remove it
            if (searchfloorMap) {
                searchfloorMap.remove();
            }

            // Create a new Leaflet map and set the view
            searchfloorMap = L.map('searchedfloorImageContainer').setView([0, 0], 1.5);

            // Add an image overlay to the map
            L.imageOverlay(imageUrl, [[-80, -160], [80, 160]]).addTo(searchfloorMap);
        }


        function displaySearchFloorImage(floorId) {
            var floorImageContainer = document.getElementById('searchedfloorImageContainer');
            
            // Send the selected floor ID in the request body of a POST request
            $.ajax({
                type: 'POST',
                url: 'admin/get_floor_image.php',
                data: { floor_id: floorId },
                dataType: 'text', // Specify expected data type as text (the image URL)
                success: function(imageUrl) {
                    initializeViewSearchFloorMap(imageUrl);
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('AJAX error:', status, error);
                    console.log(xhr.responseText); // Log the response for debugging
                }
            });
        }

        function displaySavedRooms(floorId, roomId) {
        // Make an AJAX call to fetch saved room locations from the database
        $.ajax({
            type: 'POST',
            url: 'admin/get_specific_room.php', // Change to the actual PHP script that fetches room data from the database
            data: { floor_id: floorId, room_id: roomId }, // Pass floor_id as data
            dataType: 'json',
            success: function (response) {
                // Iterate through the response data and add markers for each saved room location
                response.forEach(function (room) {
                    var roomLatLng = L.latLng(room.latitude, room.longitude);
                    var roomMarker = L.marker(roomLatLng).addTo(searchfloorMap);

                    var popupContent = '<center><h5 style="max-width: 200px"><strong>' + room.room_name + '</strong></h5></center><br>';
                        popupContent += '<img src="admin/assets/images/' + room.room_image + '" alt="' + room.room_name + '" style="width: 100%; margin-bottom: 15px; border-radius: 5px">';
                    roomMarker.bindPopup(popupContent);

                    // Set view on marker location
                    searchfloorMap.setView(roomLatLng, 2); // 18 is the zoom level, you can adjust as needed
                });
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error('AJAX error:', status, error);
                console.log(xhr.responseText); // Log the response for debugging
            }
        });
    }

    </script>

</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</html>
