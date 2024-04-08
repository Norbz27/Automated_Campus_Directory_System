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
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
       <link rel="stylesheet" href="style.css">
       <link rel="stylesheet" href="admin/assets/css/styles.min.css" />
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
                    <input type="text" name="" id="" style="width: 300px;" class="form-control mr-2">
                    <button class="btn btn-primary mr-2" onclick="showSelectedLocation()"><i class="ti ti-location"></i></button>
                </div>

                <p id="error-message" style="color: red;"></p>
            </div>
            <div id="allLocationsMap" style="height: 600px; width: 100%;"></div>
        </div>
    </div>
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
    <script>
       function populateFloorDropdown(buildingId) {
            console.log(buildingId);
            $.ajax({
                type: 'POST', // Change to POST
                url: 'admin/get_floors.php',
                data: { building_id: buildingId }, // Pass building_id in the request body
                dataType: 'json', // Specify expected data type
                success: function(response) {
                    // Clear existing options in the dropdown
                    $('#floorDropdown').empty();

                    // Add the first option "Choose a floor"
                    $('#floorDropdown').append($('<option>', {
                        value: '',
                        text: 'Choose a floor'
                    }));

                    // Iterate over the response data and append options to the dropdown
                    $.each(response, function(index, floor) {
                        $('#floorDropdown').append($('<option>', {
                            value: floor.floor_id,
                            text: floor.name
                        }));

                        // Display the floor image for the first floor in the dropdown
                        if (index === 0) {
                            //displayFloorImage(floor.floor_id);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('AJAX error:', status, error);
                    console.log(xhr.responseText); // Log the response for debugging
                }
            });
        }
        var floorMap; // Declare floorMap globally or in a scope accessible to your functions

        function initializeViewFloorMap(imageUrl) {
            // Check if floorMap already exists; if so, remove it
            if (floorMap) {
                floorMap.remove();
            }

            // Create a new Leaflet map and set the view
            floorMap = L.map('floorImageContainer').setView([0, 0], 1.5);

            // Add an image overlay to the map
            L.imageOverlay(imageUrl, [[-80, -160], [80, 160]]).addTo(floorMap);
        }


        function displayFloorImage(floorId) {
            var floorImageContainer = document.getElementById('floorImageContainer');
            
            // Send the selected floor ID in the request body of a POST request
            $.ajax({
                type: 'POST',
                url: 'admin/get_floor_image.php',
                data: { floor_id: floorId },
                dataType: 'text', // Specify expected data type as text (the image URL)
                success: function(imageUrl) {
                    initializeViewFloorMap(imageUrl);
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('AJAX error:', status, error);
                    console.log(xhr.responseText); // Log the response for debugging
                }
            });
        }

        // Event listener for when a floor is selected from the dropdown
        $('#floorDropdown').on('change', function() {
            var selectedFloorId = $(this).val(); // Get the selected floor ID

            // Clear the first option "Choose a floor"
            $('#floorDropdown option:contains("Choose a floor")').remove();

            displayFloorImage(selectedFloorId);
            displaySavedRooms(selectedFloorId);
            /// Clear the existing markers and overlays on the map if floorMap is defined
            if (floorMap) {
                floorMap.eachLayer(function(layer) {
                    if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
                        floorMap.removeLayer(layer);
                    }
                });
            }

        });

        function displaySavedRooms(floorId) {
            // Make an AJAX call to fetch saved room locations from the database
            $.ajax({
                type: 'POST',
                url: 'admin/get_all_rooms.php', // Change to the actual PHP script that fetches room data from the database
                data: { floor_id: floorId }, // Pass floor_id as data
                dataType: 'json',
                success: function(response) {
                    // Iterate through the response data and add markers for each saved room location
                    response.forEach(function(room) {
                        var roomLatLng = L.latLng(room.latitude, room.longitude);
                        var roomMarker = L.marker(roomLatLng).addTo(floorMap);

                        var popupContent = '<center><h5><strong>' + room.room_name + '</strong></h5></center><br>';
                        popupContent += '<img src="admin/assets/images/' + room.room_image + '" alt="' + room.room_name + '" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px">';
                        roomMarker.bindPopup(popupContent);
                    });
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('AJAX error:', status, error);
                    console.log(xhr.responseText); // Log the response for debugging
                }
            });
        }

</script>
    <script>
        var allLocationsMap;
        var allLocationsInfowindows = [];
        
        function initAllLocationsMap() {
            var surigaoCenter = {lat: 9.780337119613598, lng: 125.48353436162861}; // Coordinates for Surigao Education Center
            allLocationsMap = new google.maps.Map(document.getElementById('allLocationsMap'), {
                center: surigaoCenter,
                zoom: 20 // Adjust the zoom level as needed
            });

            // Fetch locations from the database and add markers
            fetchAllLocations();
        }

        function fetchAllLocations() {
            // Send an AJAX request to fetch all locations from the database
            $.ajax({
                type: 'GET',
                url: 'admin/get_all_building_locations.php',
                success: function(response) {
                    var locations = JSON.parse(response);
                    locations.forEach(function(location) {
                        addMarker(location);
                    });
                }
            });
        }

        function addMarker(location) {
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(location.latitude), lng: parseFloat(location.longitude)},
                map: allLocationsMap
            });

            var infowindowContent = '<center><h5><strong>' + location.label + '</strong></h5></center>';
            
            // Check if location image exists
            if (location.location_image !== null && location.location_image !== '') {
                infowindowContent += '<center><img src="admin/assets/images/' + location.building_image + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
            }
            
            infowindowContent += '<div style="text-align: right;">';
            infowindowContent += '<button class="btn btn-info btn-sm" title="View" data-toggle="modal" data-target="#viewFloorModal" onclick="populateFloorDropdown(' + location.building_id + ')"><i class="ti ti-eye"></i></button> ';     // Pass buildingId to the function
            infowindowContent += '</div>';

            var infowindow = new google.maps.InfoWindow({
                content: infowindowContent
            });

            allLocationsInfowindows.push(infowindow);

            marker.addListener('click', function() {
                closeAllInfowindows();
                infowindow.open(allLocationsMap, marker);
            });
        }


        // Function to set the buildingId before opening the modal
        function setBuildingId(buildingId) {
            document.getElementById('buildingId').value = buildingId;
        }

        function closeAllInfowindows() {
            allLocationsInfowindows.forEach(function(infowindow) {
                infowindow.close();
            });
        }

        function deleteLocation(label) {
            // Set the label value in the hidden input field
            document.getElementById('deleteLocationLabel').value = label;
            // Submit the form
            document.getElementById('deleteLocationForm').submit();
        }

        $(document).ready(function() {
            initAllLocationsMap();
        });
    </script>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</html>
