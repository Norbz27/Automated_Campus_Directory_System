<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Input Page</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAm4XE_BJt3LlMcvJi1erXZcY7Ln8xA-qg&callback=initMap"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="navigation-page-container">
        <h1>Navigation Input</h1>
        <div class="navigation-page">
            <h2>Select a Location:</h2>
            <select id="locations-dropdown">
                <option value="">Select a Location</option>
                <!-- Dropdown options will be populated dynamically -->
            </select>
            <button onclick="showSelectedLocation()">Show Location</button>
            <button onclick="calculateAndDisplayDirections()">Get Directions</button>
            <p id="error-message" style="color: red;"></p>
        </div>
        <div id="map"></div>
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
                        option.setAttribute('data-lng', location.longitude); // Add longitude as data attribute
                        dropdown.appendChild(option);
                    });
                }
            });
        }

        function showSelectedLocation() {
            var selectedOption = document.getElementById('locations-dropdown').options[document.getElementById('locations-dropdown').selectedIndex];
            var destinationLat = parseFloat(selectedOption.getAttribute('data-lat'));
            var destinationLng = parseFloat(selectedOption.getAttribute('data-lng'));

            if (!isNaN(destinationLat) && !isNaN(destinationLng)) {
                var destination = { lat: destinationLat, lng: destinationLng };

                // Initialize the map centered on the selected location
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 15,
                    center: destination
                });

                // Add a marker at the selected location
                var marker = new google.maps.Marker({
                    position: destination,
                    map: map,
                    title: selectedOption.value
                });
            } else {
                showError('Invalid coordinates for the selected location.');
            }
        }

        function calculateAndDisplayDirections() {
            var selectedOption = document.getElementById('locations-dropdown').options[document.getElementById('locations-dropdown').selectedIndex];
            var destinationLat = parseFloat(selectedOption.getAttribute('data-lat'));
            var destinationLng = parseFloat(selectedOption.getAttribute('data-lng'));

            if (!isNaN(destinationLat) && !isNaN(destinationLng)) {
                var destination = { lat: destinationLat, lng: destinationLng };

                // Get user's current location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var origin = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
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
</html>
