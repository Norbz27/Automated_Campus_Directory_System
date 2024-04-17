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
    $login_attempts_stmt->close();
    
    if ($count >= 3) {
        header("Location: login.php?error=loginblocked");
        exit();
    }
    
    $insert_time = time();
    $insert_stmt = $conn->prepare("INSERT INTO ip_details (ip, login_time) VALUES (?, ?)");
    $insert_stmt->bind_param("si", $ip, $insert_time);
    $insert_stmt->execute();
    $insert_stmt->close();   
}

function loginUser($username, $password){
    global $conn;

    $sql = "SELECT * FROM tbl_account WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $_SESSION['account_id'] = $user['account_id'];
        $_SESSION['username'] = $user['username']; 

        $ip = $_SERVER['REMOTE_ADDR'];
        $delete_stmt = $conn->prepare("DELETE FROM ip_details WHERE ip = ?");
        $delete_stmt->bind_param("s", $ip);
        $delete_stmt->execute();
        $delete_stmt->close();

        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalidlogin");    
    }

    $stmt->close();
}