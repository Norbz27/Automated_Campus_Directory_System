<?php
include_once 'db_con/db.php';

// Check if building_id is set and not empty
if(isset($_GET['building_id']) && !empty($_GET['building_id'])) {
    $buildingId = $_GET['building_id'];

    // Establish database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to fetch building coordinates
    $sql = "SELECT label, building_image, latitude, longitude FROM tbl_building WHERE building_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error in prepared statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $buildingId);

    // Execute query
    if (!$stmt->execute()) {
        die("Error executing query: " . $stmt->error);
    }

    // Bind result variables
    $stmt->bind_result($label, $building_image, $latitude, $longitude);

    // Fetch values
    if($stmt->fetch()) {
        // Return coordinates as JSON
        echo json_encode(array("label" => $label, "building_image" => $building_image, "latitude" => $latitude, "longitude" => $longitude));
    } else {
        // No coordinates found for the given building_id
        echo json_encode(array("error" => "Building coordinates not found"));
    }

    // Close statement
    $stmt->close();

    // Close connection
    $conn->close();
} else {
    // No building_id provided
    echo json_encode(array("error" => "No building_id provided"));
}
?>
