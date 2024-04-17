<?php include_once 'header.php'?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
        .profile-picture {
        position: relative;
        overflow: hidden;
    }

    .overlay {
        position: absolute;
        top: 0;
        background-color: rgba(0, 0, 0, 0.5); /* Adjust the opacity as needed */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        width: 180px; 
        height: 180px;
        border-radius: 5px;
        cursor: pointer;
    }

    .profile-picture:hover .overlay {
        opacity: 1;
    }
    .form-label{
        font-size: 14px;
    }
</style>

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

            var infowindowContent = '<form id="edit_form" enctype="multipart/form-data">';
                infowindowContent += '<input type="hidden" class="form-control form-control-sm mb-2" value="'+ location.location_id +'" style="width:auto;" name="edit_location_id">';
                infowindowContent += '<input type="text" class="form-control form-control-sm mb-2" value="'+ location.label +'" style="width:auto;" disabled id="edit_location_label" name="edit_location_label">';
                infowindowContent += '<div class="profile-picture">';
                infowindowContent += '<label for="adprofile">';
            
            // Check if location image exists
            if (location.location_image !== null && location.location_image !== '') {
                infowindowContent += '<img src="assets/images/' + location.location_image + '" alt="location Name" id="adprofilePreview" style="width:180px; height: 180px; object-fit: cover; margin-bottom: 15px; border-radius: 5px">';
            }
            
            infowindowContent += '<div class="overlay">';
            infowindowContent += '<p style="font-size: 14px">Upload new profile</p>';
            infowindowContent += '</div>';
            infowindowContent += '<input type="file" name="adprofile" id="adprofile" onchange="previewImageAdd()" accept="image/*" hidden disabled>';
            infowindowContent += '<input type="hidden" value="'+ location.location_image +'" name="edit_location_img">';
            infowindowContent += '</label>';
            infowindowContent += '</div>';
            infowindowContent += '<div style="text-align: center;">';
            infowindowContent += '<button type="button" class="btn btn-primary btn-sm mr-1" id="edit_btn" onclick="editBtn()">Edit Location</button>';
            infowindowContent += '<button type="submit" name="submit" class="btn btn-primary btn-sm d-none mr-1" id="save_btn" style="margin-left: 5px;">Save</button>'; // Added margin-left
            infowindowContent += '<button class="btn btn-primary btn-sm ml-2" onclick="deleteLocation(\'' + location.label + '\')"><i class="ti ti-trash"></i></button>';
            infowindowContent += '</div>';
            infowindowContent += '</form>';


            var infowindow = new google.maps.InfoWindow({
                content: infowindowContent
            });

            allLocationsInfowindows.push(infowindow);

            marker.addListener('click', function() {
                closeAllInfowindows();
                infowindow.open(allLocationsMap, marker);
            });
        }

        function editBtn(){
            $('#edit_btn').addClass('d-none');
            $('#save_btn').removeClass('d-none');
            $('#edit_room_num').prop('disabled', false);
            $('#edit_location_label').prop('disabled', false);
            $('#adprofile').prop('disabled', false);
        }

        function previewImage() {
            const fileInput = document.getElementById('viewprofile');
            const img = document.getElementById('profilePic');
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }

        function previewImageAdd() {
            const fileInput = document.getElementById('adprofile');
            const img = document.getElementById('adprofilePreview');
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }

        $(document).on("submit", "#edit_form", function (e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append("save_edit", true);

            $.ajax({
                type: "POST",
                url: "save_edit_location.php",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                var res = JSON.parse(response);
                if (res.status == 404) {
                    $('#edit_btn').removeClass('d-none');
                    $('#save_btn').addClass('d-none');
                    $('#edit_room_num').prop('disabled', true);
                    $('#edit_location_label').prop('disabled', true);
                    $('#adprofile').prop('disabled', true);
                } else if (res.status == 200) {
                    $('#edit_btn').removeClass('d-none');
                    $('#save_btn').addClass('d-none');
                    $('#edit_room_num').prop('disabled', true);
                    $('#edit_location_label').prop('disabled', true);
                    $('#adprofile').prop('disabled', true);
                }
                },
            });
        });


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