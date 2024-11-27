<?php
include 'config.php';

// Get current date and time
$current_datetime = date('Y-m-d H:i:s');

// Prepare the update query
$update_query = "UPDATE borrowingtransactions 
                 SET status = 'overdue' 
                 WHERE due_date < ? AND status = 'borrowed'";

$stmt = $conn->prepare($update_query);
if ($stmt) {
    $stmt->bind_param("s", $current_datetime);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "Overdue statuses updated successfully.";
    } else {
        echo "No overdue transactions found.";
    }

    $stmt->close();
} else {
    error_log("Prepare failed: " . $conn->error);
}

$conn->close();
?>