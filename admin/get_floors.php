<?php
include_once 'db_con/db.php'; // Include your database connection file

// Retrieve the building ID from the GET parameter
if(isset($_POST['building_id'])) {
    $building_id = $_POST['building_id'];
    
    // Query to fetch the floors based on the building ID
    $query = "SELECT floor_id, name FROM tbl_floors WHERE building_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("i", $building_id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Initialize an array to store the floors
    $floors = array();

    // Fetch all the floors
    while ($row = $result->fetch_assoc()) {
        $floors[] = $row;
    }

    // Convert the result to JSON format and output it
    echo json_encode($floors);

    // Close the statement
    $stmt->close();
} else {
    // Handle the case when building ID is not provided
    echo 'error';
}
?>
