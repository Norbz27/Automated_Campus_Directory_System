<!DOCTYPE html>
<html>
<head>
  <title>Floor Map with Route</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
</head>
<body>
<button class="btn btn-info btn-sm" title="View" data-toggle="modal" data-target="#viewFloorModal">Show</button>
  <!-- Modal for viewing floors -->
  <div class="modal fade" id="viewFloorModal" tabindex="-1" role="dialog" aria-labelledby="viewFloorModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
                    <div id="map" style="width: 100%; height: 400px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
  <script>
      // app.js
      $(document).ready(function() {
          const map = L.map('map').setView([0, 0], 1);

          const floorMap = L.imageOverlay('/automated_campus_directory_system/admin/assets/images/floor1.jpg', [[-80, -160], [80, 160]]).addTo(map);

          // Add a marker on the right side of the floor map
          const marker = L.marker([50, 0]).addTo(map);
          marker.setLatLng([60, 0]);

          $('#viewFloorModal').on('shown.bs.modal', function () {
            map.invalidateSize();
          });
      });
  </script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
