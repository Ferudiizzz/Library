<?php
ob_start();
session_start(); 
include 'config.php';
$ERROR = " "; 

// Handle login functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists in the database
    $sql = "SELECT admin_id, password, Status FROM admin WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['Status'] == 1) {
            echo '<script>alert("Account is already logged in")</script>';
        } elseif ($password == $row['password']) { // Simplified password check
            $_SESSION['admin_id'] = $row['admin_id'];
            $status = "UPDATE admin SET Status = 1 WHERE admin_id = " . $row['admin_id'];
            if ($conn->query($status) === TRUE) {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            echo 'Invalid email or password!';
        }
    } else {
        echo 'Invalid email or password!';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="login.css">
    <style>
        body {
            background-image:  url(Logo/bg.png);
            background-size: cover;
            background-position: center;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .custom-container {
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            background-color: #ffffff; /* Set to white */
            color: #000000;
        }

        .logo {
            width: 100px;
        }
        button {
            background-color: #d9870d;
            border: none;
            color: #fff;
        }
        button:hover {
            background-color: #000000;
        }
        .text-bg-secondary {
            color: bluaqua !important;
        }
        .text-bg-secondary:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="text-center mb-4">
        <img src="Logo/tupi.png" alt="Logo" class="logo mb-1">
        <h3>Tupi National High School</h3>
    </div>
    <div class="custom-container mx-auto">
        <h2 class="text-center mb-4">Login</h2>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" name="submit">Login</button>
        </form>

        <div class="text-center mt-3">
            Don't have an account? <a href="register.php" class="text-bg-secondary ">Register Here</a><br>
            Forgot Password? <a href="forgot_password.php" class="text-bg-secondary ">Click Here</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
