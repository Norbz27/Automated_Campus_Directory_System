<?php
include_once 'db_con/db.php'; // Include your database connection file

// Select all locations from the tbl_locations table
$sql = "SELECT * FROM tbl_building";
$result = $conn->query($sql);

// Check if any locations are found
if ($result->num_rows > 0) {
    // Output data of each row
    $locations = array();
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    // Return the locations data as JSON
    echo json_encode($locations);
} else {
    echo "0 results";
}
$conn->close();
?>
