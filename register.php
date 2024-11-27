<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $middle_name = isset($_POST['no_middle_name']) ? 'N/A' : $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $target_dir = "upload/";
    $photo = $target_dir . basename($_FILES["photo"]["name"]);
    move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);

    $sql = "INSERT INTO admin (name, middle_name, last_name, age, gender, birthday, contact_number, address, photo, email, password, login_attempts, Status) 
            VALUES ('$name', '$middle_name', '$last_name', '$age', '$gender', '$birthday', '$contact_number', '$address', '$photo', '$email', '$password', 0, 1)";

    if ($conn->query($sql) === TRUE) {
        // Automatically log the user in and redirect to the dashboard
        $user_id = $conn->insert_id;
        session_start();
        $_SESSION['admin_id'] = $admin_id;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="assets/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .h-custom {
            height: 100vh;
        }
        .logo {
            position: absolute;
            top: 10px;
            left: 20px; 
            max-width: 200px; 
        }
        input, textarea {
            border: 2px solid red;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 10px;
        }
        /* Green outline for valid inputs */
        .is-valid {
            border-color: green;
        }
        /* Red outline for invalid inputs */
        .is-invalid {
            border-color: red;
        }
        input[disabled], textarea[disabled] {
            background-color: #e9ecef;
        }
    </style>
    <script>
    function toggleMiddleNameField() {
        const middleNameField = document.querySelector('input[name="middle_name"]');
        const noMiddleNameCheckbox = document.querySelector('input[name="no_middle_name"]');

        if (noMiddleNameCheckbox.checked) { 
            middleNameField.value = 'N/A';
            middleNameField.disabled = true;
            middleNameField.classList.add('is-valid');
            middleNameField.classList.remove('is-invalid');
        } else {
            middleNameField.value = '';
            middleNameField.disabled = false;
            middleNameField.classList.remove('is-valid');
            middleNameField.classList.remove('is-invalid');
        }
        validateForm();
    }

    function validateForm() {
        const formElements = document.querySelectorAll('input, textarea');
        let isFormComplete = true;

        formElements.forEach(element => {
            if (element.required && !element.disabled) {
                if (element.value.trim() === '') {
                    element.classList.remove('is-valid');
                    element.classList.add('is-invalid');
                    isFormComplete = false;
                } else {
                    element.classList.remove('is-invalid');
                    element.classList.add('is-valid');
                }
            } else if (element.disabled && element.value.trim() === 'N/A') {
                element.classList.remove('is-invalid');
                element.classList.add('is-valid');
            }
        });

        // Ensure the submit button has the correct state
        document.querySelector('button[type="submit"]').disabled = !isFormComplete;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initial form validation
        validateForm();

        // Add event listener for input changes
        document.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('input', validateForm);
        });

        // Add event listener for checkbox change
        document.querySelector('input[name="no_middle_name"]').addEventListener('change', toggleMiddleNameField);
    });
    </script>
</head>
<body>
    <section class="vh-100">
        <div class="container-fluid h-custom">
                <div class="col-md-8 col-lg-6 col-xl-5 offset-xl-1" style="zoom: 80%;">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start mb-4">
                        <div class="h4">Register</div>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <div class="d-flex flex-row">
                                    <input type="text" class="form-control me-1" name="middle_name" id="middle_name" required>
                                    <div class="form-check me-1">
                            <input type="checkbox" name="no_middle_name"  class="form-control-input  " value="1" onchange="toggleMiddleNameField()">    
                            </div>
                            
                            <div class="form-check me-1">
                            <label class="form-check-label" for="male">     N/A
                            </label>
                            </div>
                           </div>
                        </div>
                            <div class="col-md-5">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" id="age" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Gender</label>
                                <div class="d-flex flex-row">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="gender" id="male" value="Male">
                                        <label class="form-check-label" for="male">Male</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="gender" id="female" value="Female">
                                        <label class="form-check-label" for="female">Female</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="other" value="other">
                                        <label class="form-check-label" for="other">Other</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="birthday" class="form-label">Birthday</label>
                                <input type="date" class="form-control" name="birthday" id="birthday" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="tel" name="contact_number" id="contact_number" required
                                    pattern="\d{11}" maxlength="11" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" id="address" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Photo</label>
                                <input type="file" class="form-control" name="photo" id="photo" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                            </div>
                        </div>

                        <div class="pt-1 mb-4 text-center">
                            <button class="btn btn-primary btn-lg btn-block" type="submit" name="submit" value="submit">Register</button>
                        </div>
                    </form>
                    <p class="small fw-bold mt-2 pt-1 mb-0 text-lg-start ">Already have an account? <a href="login.php"
                    class="link-danger">Login now</a></p>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

