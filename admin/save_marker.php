<?php
include_once 'db_con/db.php';
// Retrieve data from the AJAX request
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$description = $_POST['description'];


// Prepare and execute the SQL statement to insert the data
$stmt = $conn->prepare("INSERT INTO tbl_locations (label, latitude, longitude) VALUES (?, ?, ?)");
$stmt->bind_param("sdd", $description, $latitude, $longitude);
$stmt->execute();

// Close the statement and connection
$stmt->close();
$conn->close();
?>
