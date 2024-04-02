<?php
include_once 'db_con/db.php';

// Retrieve data from the AJAX request
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$description = $_POST['description'];
$locationImage = '';

// Handle image upload
if(isset($_FILES['locationImage']) && $_FILES['locationImage']['error'] == UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['locationImage']['tmp_name'];
    $filename = $_FILES['locationImage']['name'];
    $locationImage = 'assets/images/' . $filename;
    if (move_uploaded_file($tmp_name, $locationImage)) {
        // Prepare and execute the SQL statement to insert the data
        $stmt = $conn->prepare("INSERT INTO tbl_locations (label, latitude, longitude, location_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $description, $latitude, $longitude, $filename);
        if ($stmt->execute()) {
            // Close the statement
            $stmt->close();
            // Close the connection
            $conn->close();
            // Return success message
            echo json_encode(array("status" => "success", "message" => "Location saved successfully."));
            exit;
        } else {
            // Error in executing SQL statement
            echo json_encode(array("status" => "error", "message" => "Failed to save location to database."));
            exit;
        }
    } else {
        // Error in moving uploaded file
        echo json_encode(array("status" => "error", "message" => "Failed to upload location image."));
        exit;
    }
} else {
    // No image uploaded
    echo json_encode(array("status" => "error", "message" => "No image uploaded."));
    exit;
}
?>
