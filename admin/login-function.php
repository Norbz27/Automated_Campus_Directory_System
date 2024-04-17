<?php
session_start(); // Start the session

include 'db_con/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    trackLoginAttempts($_SERVER['REMOTE_ADDR']);

    loginUser($username, $password);

}

function trackLoginAttempts($ip){
    global $conn;

    $login_time = time() - 60;
    $login_attempts_stmt = $conn->prepare("SELECT COUNT(*) AS total_count FROM ip_details WHERE ip = ? AND login_time > ?");
    $login_attempts_stmt->bind_param("si", $ip, $login_time);
    $login_attempts_stmt->execute();
    $res = $login_attempts_stmt->get_result();
    $count = $res->fetch_assoc()['total_count'];
    $login_attempts_stmt->close(); // Close the prepared statement
    
    if ($count >= 3) {
        header("Location: login.php?error=loginblocked");
        exit();
    }
    
    $insert_time = time(); // Store the current time in a variable
    $insert_stmt = $conn->prepare("INSERT INTO ip_details (ip, login_time) VALUES (?, ?)");
    $insert_stmt->bind_param("si", $ip, $insert_time); // Bind parameters using the variable
    $insert_stmt->execute();
    $insert_stmt->close(); // Close the prepared statement    
}

function loginUser($username, $password){
    global $conn;
    // Prepare SQL statement to fetch user from database (using prepared statement)
    $sql = "SELECT * FROM tbl_account WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // User is authenticated, set session variables and redirect
        $user = $result->fetch_assoc();
        $_SESSION['account_id'] = $user['account_id']; // Assuming user_id is the primary key of tbl_account
        $_SESSION['username'] = $user['username']; // Store username in session
        // You can store more user data in session if needed

        // Delete IP details (assuming you have an appropriate table structure)
        $delete_stmt = $conn->prepare("DELETE FROM ip_details WHERE ip = ?");
        $delete_stmt->bind_param("s", $ip);
        $delete_stmt->execute();
        $delete_stmt->close(); // Close the prepared statement

        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalidlogin");    
    }

    // Close the main prepared statement
    $stmt->close();
}