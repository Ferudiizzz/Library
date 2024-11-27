<?php
include 'config.php';

if (isset($_GET['return'])) {
    $transaction_id = mysqli_real_escape_string($conn, $_GET['return']);
    
    // Get transaction details
    $query = "SELECT * FROM borrowingtransactions WHERE transaction_id = '$transaction_id'";
    $result = mysqli_query($conn, $query);
    $transaction = mysqli_fetch_assoc($result);
    
    // Calculate overdue days
    $due_date = new DateTime($transaction['due_date']);
    $return_date = new DateTime();
    $diff = $return_date->diff($due_date);
    $overdue_days = ($return_date > $due_date) ? $diff->days : 0;
    
    // Update transaction
    $return_date_str = date('Y-m-d H:i:s');
    $status = $overdue_days > 0 ? 'overdue' : 'returned';
    
    mysqli_query($conn, "UPDATE borrowingtransactions 
                        SET return_date = '$return_date_str', 
                            status = '$status' 
                        WHERE transaction_id = '$transaction_id'");
    
    // Update book status
    mysqli_query($conn, "UPDATE books 
                        SET status = 'available', 
                            available_copies = available_copies + 1 
                        WHERE book_id = '{$transaction['book_id']}'");
    
    // Add penalty if overdue
    if ($overdue_days > 0) {
        $penalty_fee = $overdue_days * 1.00; // $1 per day
        mysqli_query($conn, "INSERT INTO penalties (transaction_id, overdue_days, penalty_fee) 
                            VALUES ('$transaction_id', '$overdue_days', '$penalty_fee')");
    }
    
    echo "success";  // Return success message
} else {
    echo "error";  // Error if no transaction ID is provided
}

mysqli_close($conn);
?>
