<!DOCTYPE html>
<html>
<head>
    <title>My Location</title>
    <!-- Load the Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAm4XE_BJt3LlMcvJi1erXZcY7Ln8xA-qg&libraries=places"></script>
    <script>
        // Function to initialize the map
        function initMap() {
            // Create a map centered at the user's current location
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: {lat: 0, lng: 0} // Initial center, will be updated later
            });

            // Try HTML5 geolocation to get user's current position
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Center the map on the user's location
                    map.setCenter(pos);

                    // Create a marker at the user's location
                    var marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: 'Your Location'
                    });
                }, function() {
                    // Handle geolocation errors
                    handleLocationError(true, map.getCenter());
                });
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, map.getCenter());
            }
        }

        // Function to handle geolocation errors
        function handleLocationError(browserHasGeolocation, pos) {
            var infoWindow = new google.maps.InfoWindow({map: map});
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation ?
                                  'Error: The Geolocation service failed.' :
                                  'Error: Your browser doesn\'t support geolocation.');
        }
    </script>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>My Location</h1>
    <div id="map"></div>
    <script>
        // Call the initMap function when the page is loaded
        initMap();
    </script>
</body>
</html>
