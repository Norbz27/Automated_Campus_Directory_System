<?php
include_once 'db_con/db.php'; // Include your database connection file

// Check if floor_id is provided
if (isset($_POST['floor_id'])) {
    $floorId = $_POST['floor_id'];

    // Query to retrieve saved room locations based on floor_id
    $sql = "SELECT * FROM tbl_rooms WHERE floor_id = $floorId";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Initialize an array to store room data
        $rooms = array();

        // Fetch rows and add them to the rooms array
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }

        // Close the database connection
        $conn->close();

        // Encode the rooms array as JSON and echo it
        echo json_encode($rooms);
    } else {
        // No rooms found
        echo json_encode(array());
    }
} else {
    // Floor_id not provided
    echo json_encode(array());
}
?>
