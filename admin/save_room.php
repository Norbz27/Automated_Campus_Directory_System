<?php
include_once 'db_con/db.php';
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all necessary data is provided
    if (isset($_POST['room_name']) && isset($_POST['longitude']) && isset($_POST['latitude']) && isset($_POST['floor_id']) && isset($_FILES['room_image'])) {
        
        // Prepare data for insertion
        $roomName = $_POST['room_name'];
        $longitude = $_POST['longitude'];
        $latitude = $_POST['latitude'];
        $floorId = $_POST['floor_id'];
        
        // Process and move uploaded image file
        $targetDir = "assets/images"; // Specify the directory where you want to save uploaded images
        $targetFile = $targetDir . basename($_FILES["room_image"]["name"]);
        
        if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $targetFile)) {
            // Image uploaded successfully, proceed with database insertion
            $roomImage = $_FILES["room_image"]["name"];
            
            // Prepare SQL statement
            $sql = "INSERT INTO tbl_rooms (room_name, longitude, latitude, room_image, floor_id)
                    VALUES ('$roomName', '$longitude', '$latitude', '$roomImage', '$floorId')";
            
            if ($conn->query($sql) === TRUE) {
                echo json_encode(array("status" => "success", "message" => "Location saved successfully."));
                exit;
            } else {
                echo json_encode(array("status" => "error", "message" => "Failed to save location to database."));
                exit;
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to upload location image."));
            exit;
        }
        
        // Close connection
        $conn->close();
    } else {
        echo json_encode(array("status" => "error", "message" => "Required data is missing."));
        exit;
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Invalid request method."));
    exit;
}
?>
