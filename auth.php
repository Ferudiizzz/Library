<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Optionally, store the requested page to redirect after login
    // $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    echo "Login first.";
    exit();
}

// Optionally, retrieve user details from the database
require 'config.php';
$stmt = $conn->prepare("SELECT name, middle_name, last_name FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$stmt->bind_result($name, $middle_name, $last_name);
$stmt->fetch();
$stmt->close();
$conn->close();
?>