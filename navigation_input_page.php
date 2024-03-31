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
            <h2>Select a Location or Enter a Specific Destination:</h2>
            <select id="locations-dropdown">
                <option value="">Select a Location</option>
                <!-- Dropdown options will be populated dynamically -->
            </select>
            <input type="text" id="manual-input" placeholder="Enter a location or room number/name">
            <button onclick="calculateAndDisplayDirections()">Navigate</button>
        </div>
        <div id="map"></div>
    </div>

    <script>
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
                        option.setAttribute('data-label', location.label); // Add label as data attribute
                        dropdown.appendChild(option);
                    });
                }
            });
        }

        function calculateAndDisplayDirections() {
            var selectedOption = document.getElementById('locations-dropdown').options[document.getElementById('locations-dropdown').selectedIndex];
            var destinationLat = parseFloat(selectedOption.getAttribute('data-lat'));
            var destinationLng = parseFloat(selectedOption.getAttribute('data-lng'));
            var destinationLabel = selectedOption.getAttribute('data-label');

            if (!isNaN(destinationLat) && !isNaN(destinationLng)) {
                var destination = { lat: destinationLat, lng: destinationLng };

                // Get user's current location
                getUserLocation(destination, destinationLabel);
            } else {
                alert('Invalid coordinates for the selected location.');
            }
        }

        function getUserLocation(destination, destinationLabel) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var origin = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Calculate directions between user's location and destination
                    calculateDirections(origin, destination, destinationLabel);
                }, function() {
                    // Handle geolocation errors
                    handleLocationError(true, map.getCenter());
                });
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, map.getCenter());
            }
        }

        function calculateDirections(origin, destination, destinationLabel) {
            var directionsService = new google.maps.DirectionsService();
            var directionsRenderer = new google.maps.DirectionsRenderer();
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 7,
                center: origin
            });
            directionsRenderer.setMap(map);

            var request = {
                origin: origin,
                destination: destination,
                travelMode: 'DRIVING'
            };

            directionsService.route(request, function(result, status) {
                if (status == 'OK') {
                    directionsRenderer.setDirections(result);

                    // Display the label on the selected location marker
                    var marker = new google.maps.Marker({
                        position: destination,
                        map: map,
                        title: destinationLabel // Set the label as the marker's title
                    });
                } else {
                    alert('Directions request failed due to ' + status);
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

        // Calling the function to show the navigation page immediately upon loading
        showNavigationPage();
    </script>
</body>
</html>
