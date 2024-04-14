<?php
include_once 'db_con/db.php';

// Retrieve building coordinates based on building_id
if(isset($_GET['building_id']) && !empty($_GET['building_id'])) {
    $buildingId = $_GET['building_id'];
    
    // Prepare SQL statement to fetch building coordinates
    $stmt = $conn->prepare("SELECT latitude, longitude FROM tbl_building WHERE building_id = ?");
    $stmt->bind_param("i", $buildingId);
    
    // Execute query
    $stmt->execute();
    
    // Bind result variables
    $stmt->bind_result($latitude, $longitude);
    
    // Fetch values
    if($stmt->fetch()) {
        // Return coordinates as JSON
        echo json_encode(array("latitude" => $latitude, "longitude" => $longitude));
    } else {
        // No coordinates found for the given building_id
        echo json_encode(array("error" => "Building coordinates not found"));
    }
    
    // Close statement
    $stmt->close();
} else {
    // No building_id provided
    echo json_encode(array("error" => "No building_id provided"));
}

// Close connection
$conn->close();
?>
