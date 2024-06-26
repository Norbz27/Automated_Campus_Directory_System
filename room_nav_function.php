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
                            displayFloorImage(null);
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
            
            if(floorId == null){
                setTimeout(function() {
                    initializeViewFloorMap('admin/assets/images/placeholder/placeholder.webp');
                }, 500);
                
            }else{
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
        }

        // Event listener for when a floor is selected from the dropdown
        $('#floorDropdown').on('change', function() {
            var selectedFloorId = $(this).val(); // Get the selected floor ID

            // Clear the first option "Choose a floor"
            $('#floorDropdown option:contains("Choose a floor")').remove();
            console.log(selectedFloorId);
            setTimeout(function() {
                displayFloorImage(selectedFloorId);
                setTimeout(function() {
                    displayAllSavedRooms(selectedFloorId);
                }, 500);
            }, 500);

            /// Clear the existing markers and overlays on the map if floorMap is defined
            if (floorMap) {
                floorMap.eachLayer(function(layer) {
                    if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
                        floorMap.removeLayer(layer);
                    }
                });
            }

        });

        function displayAllSavedRooms(floorId) {
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

                        var popupContent = '<center><h5 style="max-width: 200px"><strong>' + room.room_name + '</strong></h5></center><br>';
                        popupContent += '<img src="admin/assets/images/' + room.room_image + '" alt="' + room.room_name + '" style="width: 100%; margin-bottom: 15px; border-radius: 5px">';
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
    var marker = [];
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

    var allMarkers = []; // Array to hold all markers

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
        allMarkers.push(marker); // Add the marker to the array

        marker.addListener('click', function() {
            closeAllInfowindows();
            infowindow.open(allLocationsMap, marker);
        });
    }

    function clearMarkers() {
        // Loop through all existing markers and set their map property to null to remove them from the map
        allMarkers.forEach(function(marker) {
            marker.setMap(null);
        });
        // Clear the array of markers
        allMarkers = [];
    }


    var directionsRenderer;
    var userLocationMarker;

    function getDirectionsToBuilding(buildingId) {
        if (!buildingId) {
            console.error("Invalid buildingId");
            return;
        }
        
        if (directionsRenderer) {
            directionsRenderer.setMap(null);
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var origin = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var buildingLatLng;

                $.ajax({
                    type: 'GET',
                    url: 'admin/get_building_coordinates.php',
                    data: { building_id: buildingId },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.latitude && response.longitude) {
                            buildingLatLng = new google.maps.LatLng(response.latitude, response.longitude);

                            var directionsService = new google.maps.DirectionsService();
                            directionsRenderer = new google.maps.DirectionsRenderer();
                            directionsRenderer.setMap(allLocationsMap);

                            var request = {
                                origin: origin,
                                destination: buildingLatLng,
                                travelMode: google.maps.TravelMode.DRIVING
                            };
                            
                            userLocationMarker = new google.maps.Marker({
                                position: origin,
                                map: allLocationsMap,
                                title: 'Your Location'
                            });

                            directionsService.route(request, function(result, status) {
                                if (status == google.maps.DirectionsStatus.OK) {
                                    directionsRenderer.setDirections(result);
                                    
                                    var distance = result.routes[0].legs[0].distance.text;
                                    var duration = result.routes[0].legs[0].duration.text;

                                    var destinationMarker = new google.maps.Marker({
                                        position: buildingLatLng,
                                        map: allLocationsMap,
                                        title: response.label
                                    });
                                    
                                    var infowindowcalc = new google.maps.InfoWindow({
                                        content: '<strong>Distance:</strong> ' + distance + '<br><strong>Duration:</strong> ' + duration
                                    });
                                    
                                    var infowindowContent = '<center><h5><strong>' + response.label + '</strong></h5></center>';
                                        infowindowContent += '<center><img src="admin/assets/images/' + response.building_image + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
                                    
                                    var infowindow = new google.maps.InfoWindow({
                                        content: infowindowContent
                                    });
                                    infowindow.open(allLocationsMap, destinationMarker);
                                    destinationMarker.addListener('click', function() {
                                        infowindow.open(allLocationsMap, destinationMarker);
                                    });

                                    if (userLocationMarker) {
                                        infowindowcalc.open(allLocationsMap, userLocationMarker);
                                    }

                                    userLocationMarker.addListener('click', function() {
                                        infowindowcalc.open(allLocationsMap, userLocationMarker);
                                    });
                                    clearMarkers();
                                } else {
                                    console.error('Directions request failed due to ' + status);
                                }
                            });
                        } else {
                            console.error('Failed to retrieve building coordinates.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        console.log(xhr.responseText);
                    }
                });
            }, function() {
                handleLocationError(true, document.getElementById('error-message'));
            });
        } else {
            handleLocationError(false, document.getElementById('error-message'));
        }
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