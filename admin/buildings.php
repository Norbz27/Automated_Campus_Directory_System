<?php include_once 'header.php'?>

<!-- Load jQuery first -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Load Leaflet CSS -->
<!-- Load Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>

<!-- Load Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
<!-- Your custom scripts -->
<script>
    // Your custom JavaScript code here
</script>

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
<div class="row justify-content-end mb-3">
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mapModal">
                <i class="ti ti-building" style="margin-right: 5px;"></i> New Building
            </button>
        </div>
    </div>
    <div id="allLocationsMap" style="height: 600px; width: 100%;"></div>
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
                            <p>Drag & drop your imagehere or click to select files</p>
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
       function populateFloorDropdown() {
            var buildingId = $(this).data('building-id');
            console.log(buildingId);
            $.ajax({
                type: 'POST', // Change to POST
                url: 'get_floors.php',
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
         var floorMap;
        var recentMarker;

        function initializeViewFloorMap(imageUrl) {
            if (floorMap) {
                floorMap.remove();
            }

            floorMap = L.map('floorImageContainer').setView([0, 0], 1);

            // Add an image layer to the map
            var floorImage = L.imageOverlay(imageUrl, [[-80, -160], [80, 160]]).addTo(floorMap);

            // Add a click event listener to the map
            floorMap.on('click', function(e) {
                if (recentMarker) {
                    floorMap.removeLayer(recentMarker);
                }

                // Add a marker at the clicked location
                recentMarker = L.marker(e.latlng).addTo(floorMap);

                // Create a popup with an input text field
                var inputPopup = L.popup().setContent(`
                    <strong><label class="mb-1" for="markerTextInput">Set a Label:</label></strong>
                    <input class="form-control" style="width:100%" type="text" id="markerTextInput">
                    <label class="mt-3 mb-1" for="roomImage">Upload Image:</label> <input type="file" class="form-control-file" id="roomImage" accept="image/*">
                    <button type="button" class="btn btn-primary mt-4" style="width:100%" id="submitLabel" onclick="submitRoom()">Submit</button>
                `);
                // Attach the popup to the marker
                recentMarker.bindPopup(inputPopup).openPopup();

                // Add an event listener to handle marker click
                recentMarker.on('click', function(e) {
                    // Prevent map click event propagation
                    L.DomEvent.stopPropagation(e);
                });

                // Add an event listener to the popup close event
                recentMarker.getPopup().on('remove', function() {
                    floorMap.removeLayer(recentMarker); // Remove the marker when the popup is closed
                });
            });
        }


        function displayFloorImage(floorId) {
            var floorImageContainer = document.getElementById('floorImageContainer');
            
            if(floorId == null){
                setTimeout(function() {
                    initializeViewFloorMap('assets/images/placeholder/placeholder.webp');
                }, 500);
                
            }else{
                // Send the selected floor ID in the request body of a POST request
                $.ajax({
                    type: 'POST',
                    url: 'get_floor_image.php',
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

        $('#viewFloorModal').on('shown.bs.modal', function () {
            
        });
        $('#viewFloorModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var buildingId = button.data('building-id');
            populateFloorDropdown.call(button, buildingId);
        });
        // Event listener for when a floor is selected from the dropdown
        $('#floorDropdown').on('change', function() {
            var selectedFloorId = $(this).val(); // Get the selected floor ID

            // Clear the first option "Choose a floor"
            $('#floorDropdown option:contains("Choose a floor")').remove();

            displayFloorImage(selectedFloorId);
            displaySavedRooms(selectedFloorId);
            // Clear the existing markers and overlays on the map
            floorMap.eachLayer(function(layer) {
                if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
                    floorMap.removeLayer(layer);
                }
            });
        });

        function displaySavedRooms(floorId) {
            // Make an AJAX call to fetch saved room locations from the database
            $.ajax({
                type: 'POST',
                url: 'get_all_rooms.php', // Change to the actual PHP script that fetches room data from the database
                data: { floor_id: floorId }, // Pass floor_id as data
                dataType: 'json',
                success: function(response) {
                    // Iterate through the response data and add markers for each saved room location
                    response.forEach(function(room) {
                        var roomLatLng = L.latLng(room.latitude, room.longitude);
                        var roomMarker = L.marker(roomLatLng).addTo(floorMap);

                        var popupContent = '<center><h5 style="max-width: 200px"><strong>' + room.room_name + '</strong></h5></center><br>';
                        popupContent += '<img src="assets/images/' + room.room_image + '" alt="' + room.room_name + '" style="width: 100%; margin-bottom: 15px; border-radius: 5px">';
                        popupContent += '<center><button class="btn btn-primary btn-sm" onclick="deleteRoom(' + room.room_id + ', ' + room.floor_Id + ')">Delete Location</button></center>';
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

        function deleteRoom(roomId, floorId) {
            // Display a confirmation dialog using SweetAlert
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this room!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    // Make an AJAX call to delete the room
                    $.ajax({
                        type: 'POST',
                        url: 'delete_room.php', // Change to the actual PHP script that deletes the room
                        data: { room_id: roomId }, // Pass room_id as data
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                // Display a success message using SweetAlert
                                swal({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Room has been deleted.',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Refresh the map
                                        displaySavedRooms(floorId);
                                    }
                                });
                            } else {
                                // Display an error message using SweetAlert
                                swal({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            console.error('AJAX error:', status, error);
                            console.log(xhr.responseText); // Log the response for debugging
                        }
                    });
                } else {
                    // User clicked the cancel button, do nothing
                }
            });
        }


        function submitRoom() {
            // Retrieve input values
            var roomName = $('#markerTextInput').val();
            var longitude = recentMarker.getLatLng().lng;
            var latitude = recentMarker.getLatLng().lat;
            var roomImage = $('#roomImage')[0].files[0];
            var floorId = $('#floorDropdown').val();

            // Create a FormData object to send file data
            var formData = new FormData();
            formData.append('room_name', roomName);
            formData.append('longitude', longitude);
            formData.append('latitude', latitude);
            formData.append('room_image', roomImage);
            formData.append('floor_id', floorId);

            // Send data to PHP script
            $.ajax({
                type: 'POST',
                url: 'save_room.php',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        // Display a success message using SweetAlert
                        swal({
                            icon: 'success',
                            title: 'Success',
                            text: 'Room location and description saved',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Refresh the map
                                displaySavedRooms(floorId);
                            }
                        });
                        displaySavedRooms(floorId);
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
                    // Handle error
                    console.error('AJAX error:', status, error);
                    console.log(xhr.responseText); // Log the response for debugging
                }
            });
        }

</script>
  <script>
          
          $('#mapModal').on('shown.bs.modal', function () {
            initializeMap();
          });

          function initializeMap() {
            var surigaoCenter = {lat: 9.780337119613598, lng: 125.48353436162861}; // Coordinates for Surigao Education Center
            var map = new google.maps.Map(document.getElementById('map'), {
                center: surigaoCenter,
                zoom: 20 // Adjust the zoom level as needed
            });

            google.maps.event.addListener(map, 'click', function(event) {
                placeMarker(event.latLng, map);
            });
          }

          function placeMarker(location, map) {
            if (marker) {
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
                            text: 'Building location and description saved',
                        }).then((result) => {
                            location.reload();
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
            infowindowContent += '<button class="btn btn-info btn-sm" title="View" data-toggle="modal" data-target="#viewFloorModal" data-building-id="' + location.building_id + '"><i class="ti ti-eye"></i></button> ';            // Pass buildingId to the function
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
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    // Display a success message using SweetAlert
                    swal({
                        icon: 'success',
                        title: 'Success',
                        text: 'New floor added',
                    }).then(function() {
                        // Reload the page after the user confirms
                        location.reload();
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

        function initializeMap() {
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
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </div>

<?php include_once 'footer.php'?>