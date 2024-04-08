<?php
session_start(); // Start the session

include_once 'db_con/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL statement to fetch user from database
    $sql = "SELECT * FROM tbl_account WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // User is authenticated, set session variables and redirect
        $user = $result->fetch_assoc();
        $_SESSION['account_id'] = $user['account_id']; // Assuming user_id is the primary key of tbl_account
        $_SESSION['username'] = $user['username']; // Store username in session
        // You can store more user data in session if needed

        header("Location: index.php");
        exit;
    } else {
        // User credentials are invalid, display an error message
        echo "Invalid username or password";
    }

    // Close database connection
    $conn->close();
}
?>
