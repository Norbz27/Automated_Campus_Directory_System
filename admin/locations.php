<?php include_once 'header.php'?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="container-fluid">
    <!--  Row 1 -->
    <!-- Button trigger modal -->
    <div class="row justify-content-end mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mapModal">
                <i class="ti ti-location" style="margin-right: 5px;"></i> New Location
            </button>
        </div>
    </div>
    <!-- Make another map for displaying all the locations from the database -->
    <div id="allLocationsMap" style="height: 600px; width: 100%;"></div>
    
    <!-- Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New Location</h5>
                </div>
                <div class="modal-body">
                    <div id="map"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveMarker()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for deleting location -->
    <form id="deleteLocationForm" method="post" action="delete_location.php">
        <input type="hidden" id="deleteLocationLabel" name="label" value="">
    </form>

    
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
                url: 'get_all_locations.php',
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
                infowindowContent += '<center><img src="assets/images/' + location.location_image + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
            }
            
            infowindowContent += '<center><button class="btn btn-primary btn-sm" onclick="deleteLocation(\'' + location.label + '\')">Delete Location</button></center>';

            var infowindow = new google.maps.InfoWindow({
                content: infowindowContent
            });

            allLocationsInfowindows.push(infowindow);

            marker.addListener('click', function() {
                closeAllInfowindows();
                infowindow.open(allLocationsMap, marker);
            });
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

<script>
        var map;
        var infowindow;
        var markerDescriptionInput;
        var marker = null; // Keep track of the marker

        function initMap() {
            var surigaoCenter = {lat: 9.780337119613598, lng: 125.48353436162861}; // Coordinates for Surigao Education Center
            map = new google.maps.Map(document.getElementById('map'), {
                center: surigaoCenter,
                zoom: 20 // Adjust the zoom level as needed
            });

            infowindow = new google.maps.InfoWindow();

            google.maps.event.addListener(map, 'click', function(event) {
                placeMarker(event.latLng);
            });
        }

        function placeMarker(location) {
            if (marker) {
                // Remove the previously created marker from the map
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                position: location, 
                map: map
            });

            // Show input field for description when marker is clicked
            google.maps.event.addListener(marker, 'click', function() {
                var content = '<p>Set a Label:</p> <input type="text" class="form-control" id="markerDescription">';
                content += '<p class="mt-3">Upload Image:</p> <input type="file" class="form-control-file" id="locationImage" accept="image/*">';
                infowindow.setContent(content);
                infowindow.open(map, marker);
            });
        }

        function saveMarker() {
            var description = document.getElementById('markerDescription').value;
            var locationImageInput = document.getElementById('locationImage');
            var locationImage = locationImageInput.files[0]; // Get the file object
            var latitude = marker.getPosition().lat();
            var longitude = marker.getPosition().lng();
            
            // Create a FormData object to send both text and file data
            var formData = new FormData();
            formData.append('latitude', latitude);
            formData.append('longitude', longitude);
            formData.append('description', description);
            formData.append('locationImage', locationImage); // Append the file object
            
            // Send an AJAX request to a PHP script to save the marker location and description
            $.ajax({
                type: 'POST',
                url: 'save_marker.php',
                data: formData,
                contentType: false, // Set contentType to false when sending FormData
                processData: false, // Set processData to false when sending FormData
                success: function(response) {
                    // Parse the JSON response
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        // Display a success message using SweetAlert
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: 'Marker location and description saved',
                        });
                    } else {
                        // Display an error message using SweetAlert
                        swal({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Display an error message using SweetAlert
                    swal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to save marker location and description',
                    });
                }
            });
        }

    </script>
</div>
<?php include_once 'footer.php'?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
