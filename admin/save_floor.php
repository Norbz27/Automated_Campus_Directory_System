<?php
include_once 'db_con/db.php';

// Retrieve data from the AJAX request
$floorName = $_POST['floorName'];
$buildingId = $_POST['buildingId'];
$floorImage = '';

// Handle image upload
if(isset($_FILES['floorImage']) && $_FILES['floorImage']['error'] == UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['floorImage']['tmp_name'];
    $filename = $_FILES['floorImage']['name'];
    $floorImage = 'assets/images/' . $filename;
    if (move_uploaded_file($tmp_name, $floorImage)) {
        // Prepare and execute the SQL statement to insert the data
        $stmt = $conn->prepare("INSERT INTO tbl_floors (name, floor_image, building_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $floorName, $filename, $buildingId);
        if ($stmt->execute()) {
            // Close the statement
            $stmt->close();
            // Close the connection
            $conn->close();
            // Return success message
            echo json_encode(array("status" => "success", "message" => "Floor saved successfully."));
            exit;
        } else {
            // Error in executing SQL statement
            echo json_encode(array("status" => "error", "message" => "Failed to save floor to database."));
            exit;
        }
    } else {
        // Error in moving uploaded file
        echo json_encode(array("status" => "error", "message" => "Failed to upload floor image."));
        exit;
    }
} else {
    // No image uploaded
    echo json_encode(array("status" => "error", "message" => "No image uploaded."));
    exit;
}
?>
