<?php
session_start();
include 'config.php';

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];

        // Correctly use $user_id in the query
        $update_status_sql = "UPDATE admin SET Status = 0 WHERE admin_id = $admin_id";
        if ($conn->query($update_status_sql) === TRUE) {
            // Logout successful, proceed to destroy session
        } else {
            echo "Error updating status: " . $conn->error;
        }

        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

if (isset($_GET['confirm']) && $_GET['confirm'] == 'no') {
    header("Location: dashboard.php"); // Redirect to the dashboard or another page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
</head>
<body>
    <script>
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php?confirm=yes";
        } else {
            window.location.href = "logout.php?confirm=no";
        }
    </script>
</body>
</html>
