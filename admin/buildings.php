<?php include_once 'header.php'?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

<style>
    .dropzone {
        border: 2px dashed;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        width: 100%;
        height: 400px;
    }
    .dropzone:hover {
        background-color: #f8f9fa;
    }

</style>
<div class="container-fluid">
    <!--  Row 1 -->
    <!-- Button trigger modal -->
    <div class="row justify-content-end mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mapModal">
                <i class="ti ti-building" style="margin-right: 5px;"></i> New Building
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
                    <h5 class="modal-title" id="exampleModalLabel">New Building</h5>
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
    <!-- Modal for adding floors -->
    <div class="modal fade" id="addFloorModal" tabindex="-1" role="dialog" aria-labelledby="addFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFloorModalLabel">Add Floor</h5>
                </div>
                <div class="modal-body">
                    <form id="addFloorForm" enctype="multipart/form-data">
                        <label class="dropzone mb-3" for="floorImage" id="dropzone">
                            <p>Drag & drop your image here or click to select files</p>
                            <input type="file" id="floorImage" name="floorImage" class="form-control-file" accept="image/*" hidden>
                        </label>
                        <div class="form-group">
                            <label class="mb-1" for="floorName">Floor name:</label>
                            <input type="text" id="floorName" name="floorName" class="form-control">
                        </div>
                        <input type="hidden" id="buildingId" name="buildingId" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveFloor()">Save Floor</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Hidden form for deleting location -->
    <form id="deleteLocationForm" method="post" action="delete_location_building.php">
        <input type="hidden" id="deleteLocationLabel" name="label" value="">
    </form>
    <!-- Modal for viewing floors -->
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
                url: 'get_floors.php',
                data: { building_id: buildingId }, // Pass building_id in the request body
                dataType: 'json', // Specify expected data type
                success: function(response) {
                    // Clear existing options in the dropdown
                    $('#floorDropdown').empty();
                    
                    // Iterate over the response data and append options to the dropdown
                    $.each(response, function(index, floor) {
                        $('#floorDropdown').append($('<option>', {
                            value: floor.floor_id,
                            text: floor.name
                        }));
                        
                        // Display the floor image for the first floor in the dropdown
                        if (index === 0) {
                            displayFloorImage(floor.floor_id);
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

        // Function to display the selected floor's image
        function displayFloorImage(floorId) {
            var floorImageContainer = document.getElementById('floorImageContainer');
            floorImageContainer.innerHTML = ''; // Clear previous content
            
            // Send the selected floor ID in the request body of a POST request
            $.ajax({
                type: 'POST',
                url: 'get_floor_image.php',
                data: { floor_id: floorId },
                dataType: 'text', // Specify expected data type as text (the image URL)
                success: function(imageUrl) {
                    const map = L.map('floorImageContainer').setView([0, 0], 1);

                    const floorMap = L.imageOverlay('/automated_campus_directory_system/admin/assets/images/floor1.jpg', [[-80, -160], [80, 160]]).addTo(map);

                    // Add a marker on the right side of the floor map
                    const marker = L.marker([50, 0]).addTo(map);
                    marker.setLatLng([60, 0]);
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
            displayFloorImage(selectedFloorId); // Display the selected floor's image
        });

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
                url: 'get_all_building_locations.php',
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
                infowindowContent += '<center><img src="assets/images/' + location.building_image + '" alt="Location Image" style="max-width: 200px; max-height: 200px; margin-bottom: 15px; border-radius: 5px"></center>';
            }
            
            infowindowContent += '<div style="text-align: right;">';
            infowindowContent += '<button class="btn btn-primary btn-sm" onclick="deleteLocation(\'' + location.label + '\')" title="Delete"><i class="ti ti-trash"></i></button> ';
            infowindowContent += '<button class="btn btn-info btn-sm" title="View" data-toggle="modal" data-target="#viewFloorModal" onclick="populateFloorDropdown(' + location.building_id + ')"><i class="ti ti-eye"></i></button> ';
            // Pass buildingId to the function
            infowindowContent += '<button class="btn btn-success btn-sm" title="Add Floors" onclick="setBuildingId(' + location.building_id + ')" data-toggle="modal" data-target="#addFloorModal"><i class="ti ti-stairs"></i></button>';
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
<script>
    function saveFloor() {
        var floorName = document.getElementById('floorName').value;
        var floorImageInput = document.getElementById('floorImage');
        var floorImage = floorImageInput.files[0]; // Get the file object
        var buildingId = document.getElementById('buildingId').value;

        // Create a FormData object to send both text and file data
        var formData = new FormData();
        formData.append('floorName', floorName);
        formData.append('floorImage', floorImage);
        formData.append('buildingId', buildingId);

        // Send an AJAX request to a PHP script to save the floor
        $.ajax({
            type: 'POST',
            url: 'save_floor.php',
            data: formData,
            contentType: false, // Set contentType to false when sending FormData
            processData: false, // Set processData to false when sending FormData
            success: function(response) {
                // Handle the response accordingly
                // For example, you can display a success message or reload the page
                // Reload the page to reflect the changes
                location.reload();
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error(error);
            }
        });
    }
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
                url: 'save_marker_building.php',
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
    <script>
    $(document).ready(function(){
        
        // Drag and drop functionality
        var dropzone = document.getElementById('dropzone');
        var inputfile = document.getElementById("floorImage");

        dropzone.ondrop = function(e) {
            e.preventDefault();
            $('#floorImage')[0].files = e.dataTransfer.files;
            displayFileName();
        };

        dropzone.ondragover = function() {
            dropzone.style.borderColor = '#17a2b8';
            return false;
        };

        dropzone.ondragleave = function() {
            dropzone.style.borderColor = '#007bff';
            return false;
        };

        $('#floorImage').change(function() {
            //var fileName = $(this).val().split('\\').pop();
            //displayFileName(fileName);
            displayFileName();
        });

        function displayFileName() {
            // Get the file input element
            let inputfile = document.getElementById('floorImage');
            
            if (inputfile.files && inputfile.files[0]) {
                // Create object URL for the selected image
                let imglink = URL.createObjectURL(inputfile.files[0]);
                
                // Create a new image element
                let img = new Image();
                img.onload = function() {
                    // Calculate height to maintain aspect ratio
                    let aspectRatio = img.width / img.height;
                    let height = dropzone.offsetWidth / aspectRatio;

                    // Set dropzone height based on calculated height
                    dropzone.style.height = height + "px";
                    
                    // Set dropzone background image and adjust styles
                    dropzone.style.backgroundImage = `url(${imglink})`;
                    $('#dropzone p').text("");
                    dropzone.style.border = "none";
                    dropzone.style.backgroundRepeat = "no-repeat";
                    dropzone.style.backgroundSize = "cover"; // Adjust background size as needed
                };
                img.src = imglink;
            }
        }
    });
</script>
</div>
<?php include_once 'footer.php'?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
