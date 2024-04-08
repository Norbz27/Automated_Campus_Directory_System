<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Input Page</title>
    <link rel="shortcut icon" type="image/png" href="admin/assets/images/logos/logo_sec.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler-flags.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler-payments.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler-vendors.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAm4XE_BJt3LlMcvJi1erXZcY7Ln8xA-qg&callback=initMap"></script>
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
        <div class="row justify-content-end mb-3">
            <div class="col-auto">
                <a class="btn btn-none float-right" href="room_navigation.php">
                        Buildings <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-right"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M15 16l4 -4" /><path d="M15 8l4 4" /></svg>
                </a>
            </div>
        </div>
        <div class="form-con">
            <h3><b>Specific Location Navigation</b></h3>
            <div class="navigation-page">
                <h5>Select a Location:</h5>
                <select id="locations-dropdown">
                    <option value="">Select a Location</option>
                    <!-- Dropdown options will be populated dynamically -->
                </select>
                <button class="btn btn-primary mr-2" onclick="showSelectedLocation()">Show Location</button>
                <button class="btn btn-primary" onclick="calculateAndDisplayDirections()">Get Directions</button>
                <p id="error-message" style="color: red;"></p>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <script>
        var map;
        var userLocationMarker;
        var directionsService;
        var directionsRenderer;

        function showNavigationPage() {
            var navigationPage = document.querySelector('.navigation-page');
            navigationPage.style.display = 'block';
            // Call function to populate dropdown with locations
            populateDropdown();
        }

        function populateDropdown() {
            // Fetch locations from the database using AJAX
            $.ajax({
                type: 'GET',
                url: 'admin/get_all_locations.php',
                success: function(response) {
                    var locations = JSON.parse(response);
                    var dropdown = document.getElementById('locations-dropdown');
                    // Clear existing options
                    dropdown.innerHTML = '<option value="">Select a Location</option>';
                    // Add locations as options in the dropdown
                    locations.forEach(function(location) {
                        var option = document.createElement('option');
                        option.value = location.label;
                        option.textContent = location.label;
                        option.setAttribute('data-lat', location.latitude); // Add latitude as data attribute
                        option.setAttribute('data-lng', location.longitude);
                        option.setAttribute('data-img', location.location_image); // Add longitude as data attribute
                        dropdown.appendChild(option);
                    });
                }
            });
        }

        function showSelectedLocation() {
            showError('');
            var selectedOption = document.getElementById('locations-dropdown').options[document.getElementById('locations-dropdown').selectedIndex];
            var destinationLat = parseFloat(selectedOption.getAttribute('data-lat'));
            var destinationLng = parseFloat(selectedOption.getAttribute('data-lng'));
            var destinationImg = selectedOption.getAttribute('data-img');

            if (!isNaN(destinationLat) && !isNaN(destinationLng)) {
                var destination = { lat: destinationLat, lng: destinationLng };

                // Initialize the map centered on the selected location
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 20,
                    center: destination
                });

                // Add a marker at the selected location
                var marker = new google.maps.Marker({
                    position: destination,
                    map: map,
                    title: selectedOption.value
                });

                // Display location information
                var infowindowContent = '<center><h5><strong>' + selectedOption.value + '</strong></h5></center>';

                // Check if location image exists
                if (destinationImg !== null && destinationImg !== '') {
                    infowindowContent += '<center><img src="admin/assets/images/' + destinationImg + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
                }

                var infowindow = new google.maps.InfoWindow({
                    content: infowindowContent,
                    disableAutoPan: true // Prevent auto panning
                });
                infowindow.open(map, marker);
                // Open infowindow when marker is clicked
                marker.addListener('click', function() {
                    infowindow.open(map, marker);
                });

            } else {
                showError('Invalid coordinates for the selected location.');
            }
        }

        function calculateAndDisplayDirections() {
            showError('');
            var selectedOption = document.getElementById('locations-dropdown').options[document.getElementById('locations-dropdown').selectedIndex];
            var destinationLat = parseFloat(selectedOption.getAttribute('data-lat'));
            var destinationLng = parseFloat(selectedOption.getAttribute('data-lng'));
            var destinationLatsec = 9.779989735396782;
            var destinationLngsec = 125.48450870433072;

            if (!isNaN(destinationLat) && !isNaN(destinationLng)) {
                var destination = { lat: destinationLat, lng: destinationLng };
                var destinationsec = { lat: destinationLatsec, lng: destinationLngsec };

                // Get user's current location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var origin = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                            //lat: 9.780439075488488,
                            //lng: 125.48305766117328
                        };

                        // Initialize the map centered on the user's location
                        map = new google.maps.Map(document.getElementById('map'), {
                            zoom: 15,
                            center: origin
                        });

                        // Add a marker at the user's location
                        userLocationMarker = new google.maps.Marker({
                            position: origin,
                            map: map,
                            title: 'Your Location'
                        });

                        // Create the directions service and renderer
                        directionsService = new google.maps.DirectionsService();
                        directionsRenderer = new google.maps.DirectionsRenderer();
                        directionsRenderer.setMap(map);

                        // Calculate directions between user's location and destination
                        calculateDirections(origin, destination);

                        // Calculate distance and duration of the route
                        var request = {
                            origin: origin,
                            destination: destination,
                            travelMode: 'DRIVING',
                            avoidHighways: true
                        };

                        directionsService.route(request, function(result, status) {
                            if (status == 'OK') {
                                var distance = result.routes[0].legs[0].distance.text;
                                var duration = result.routes[0].legs[0].duration.text;

                                // Add a marker at the destination
                                var destinationMarker = new google.maps.Marker({
                                    position: destination,
                                    map: map,
                                    title: selectedOption.value
                                });

                                // Display location information
                                var destinationImg = selectedOption.getAttribute('data-img');
                                var infowindowContent = '<center><h5><strong>' + selectedOption.value + '</strong></h5></center>';

                                // Check if location image exists
                                if (destinationImg !== null && destinationImg !== '') {
                                    infowindowContent += '<center><img src="admin/assets/images/' + destinationImg + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
                                }

                                // Create infowindow
                                var infowindow = new google.maps.InfoWindow({
                                    content: infowindowContent
                                });
                                infowindow.open(map, destinationMarker);
                                // Open infowindow when destination marker is clicked
                                destinationMarker.addListener('click', function() {
                                    infowindow.open(map, destinationMarker);
                                });

                                // Display distance and duration in an information window
                                var infowindowcalc = new google.maps.InfoWindow({
                                    content: '<strong>Distance:</strong> ' + distance + '<br><strong>Duration:</strong> ' + duration
                                });

                                infowindowcalc.open(map, userLocationMarker);

                                userLocationMarker.addListener('click', function() {
                                    infowindowcalc.open(map, userLocationMarker);
                                });
                            } else {
                                showError('Directions request failed due to ' + status);
                            }
                        });
                    }, function() {
                        // Handle geolocation errors
                        handleLocationError(true, map.getCenter());
                    });
                } else {
                    // Browser doesn't support Geolocation
                    handleLocationError(false, map.getCenter());
                }
            } else {
                showError('Invalid coordinates for the selected location.');
            }
        }


        function calculateDirections(origin, destination) {
            var request = {
                origin: origin,
                destination: destination,
                travelMode: 'DRIVING'
            };

            directionsService.route(request, function(result, status) {
                if (status == 'OK') {
                    directionsRenderer.setDirections(result);
                } else {
                    showError('Directions request failed due to ' + status);
                }
            });
        }

        function handleLocationError(browserHasGeolocation, pos) {
            var infoWindow = new google.maps.InfoWindow({map: map});
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation ?
                                  'Error: The Geolocation service failed.' :
                                  'Error: Your browser doesn\'t support geolocation.');
        }

        function showError(message) {
            document.getElementById('error-message').textContent = message;
        }

        // Calling the function to show the navigation page immediately upon loading
        showNavigationPage();
    </script>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</html>
