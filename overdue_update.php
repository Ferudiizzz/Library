<?php
include 'config.php';

// Update statuses to 'overdue' where due_date has passed and status is 'borrowed'
$query = "UPDATE borrowingtransactions 
          SET status = 'overdue' 
          WHERE status = 'borrowed' AND due_date < NOW()";
mysqli_query($conn, $query);
?>