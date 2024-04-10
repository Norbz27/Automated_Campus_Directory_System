<?php
include_once 'db_con/db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Update query
    $sql = "UPDATE tbl_account SET name='$name', username='$username', password='$password' WHERE account_id=1"; // Assuming id=1 is the user you want to update
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to profile page after successful update
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>
