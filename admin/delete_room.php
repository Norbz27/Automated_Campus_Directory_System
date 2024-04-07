<?php
// Include database connection
include_once 'db_con/db.php';

// Check if room_id is set in POST request
if (isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];

    // Prepare and execute the SQL statement to delete the room
    $stmt = $conn->prepare("DELETE FROM tbl_rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);

    if ($stmt->execute()) {
        // If deletion is successful
        $stmt->close();
        $conn->close();
        // Return success response
        echo json_encode(array("status" => "success", "message" => "Room deleted successfully."));
        exit;
    } else {
        // If deletion fails
        // Close statement and connection
        $stmt->close();
        $conn->close();
        // Return error response
        echo json_encode(array("status" => "error", "message" => "Failed to delete room."));
        exit;
    }
} else {
    // If room_id is not set in POST request
    // Return error response
    echo json_encode(array("status" => "error", "message" => "Room ID not provided."));
    exit;
}
?>
