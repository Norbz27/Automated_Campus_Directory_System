// app.js

const map = L.map('map').setView([0, 0], 1);

const floorMap = L.imageOverlay('images/floor1.jpg', [[-80, -160], [80, 160]]).addTo(map);

// Add a marker on the right side of the floor map
const marker = L.marker([50, 0]).addTo(map);
marker.setLatLng([60, 0]);