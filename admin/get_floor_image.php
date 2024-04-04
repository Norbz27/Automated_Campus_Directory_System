<?php
include_once 'db_con/db.php'; // Include your database connection file

// Retrieve the floor ID from the POST parameter
if(isset($_POST['floor_id'])) {
    $floor_id = $_POST['floor_id'];
    
    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Query to fetch the floor image based on the floor ID
    $query = "SELECT floor_image FROM tbl_floors WHERE floor_id = ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bind_param("i", $floor_id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch the result as an associative array
    $row = $result->fetch_assoc();

    // Check if a result is found
    if($row) {
        // Return the path to the floor image
        echo '/automated_campus_directory_system/admin/assets/images/' . $row['floor_image'];
    } else {
        // Return a placeholder image or handle the case when no image is found
        echo '/automated_campus_directory_system/admin/assets/images/placeholder.jpg';
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Handle the case when floor ID is not provided
    echo 'error';
}
?>
