<?php
// delete_location.php

// Include the database connection file
include_once 'db_con/db.php';

// Check if the label is set in the POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['label'])) {
    try {
        // Perform the deletion query
        $sql = "DELETE FROM tbl_locations WHERE label = ?";
        $stmt = $conn->prepare($sql); // Prepare the statement
        $stmt->bind_param("s", $_POST['label']); // Bind parameters
        $stmt->execute(); // Execute the statement
        
        // Redirect back to the referring page with success message
        header("Location: ".$_SERVER['HTTP_REFERER']."?success=true");
        exit();
    } catch (Exception $e) {
        // Redirect back to the referring page with error message
        header("Location: ".$_SERVER['HTTP_REFERER']."?error=".$e->getMessage());
        exit();
    }
} else {
    // Redirect back to the referring page with error message for invalid request
    header("Location: ".$_SERVER['HTTP_REFERER']."?error=Invalid request");
    exit();
}
?>
