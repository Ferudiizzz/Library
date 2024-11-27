<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
   $pop = true;
   $Login = 'login.php';
}
else {
    $pop = false;
    $Login = '';
    include 'config.php';

    $admin_id = $_SESSION['admin_id']; // Corrected variable name
    $sql = "SELECT name, photo FROM admin WHERE admin_id='$admin_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $photo = $row['photo'];
    } else {
        echo "Error fetching user information!";
        exit();
    }

    $conn->close();
}

?>
<!DOCTYPE html>
<html>
    <body>
        <title>

        </title>
    </body>
    <script>
        <?php if ($pop): ?>
        window.alert("Please Login First!.");
        window.location.href = "<?php echo $Login; ?>";
        <?php endif; ?>
    </script>
</html>