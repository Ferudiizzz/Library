<?php

include 'config.php';



// Get today's date
$today = date('Y-m-d');

// Identify overdue transactions
$query = "
    SELECT 
        transaction_id, borrower_id, book_id, due_date, book_condition, status
    FROM 
        borrowingtransactions
    WHERE 
        (status = 'borrowed' OR status = 'pending_rebind') 
        AND due_date < '$today' 
        AND (return_date IS NULL)
";

$result = mysqli_query($conn, $query);
if (!$result) {
    error_log("Error fetching overdue transactions: " . mysqli_error($conn));
    mysqli_close($conn);
    exit();
}

while ($row = mysqli_fetch_assoc($result)) {
    $transaction_id = $row['transaction_id'];
    $borrower_id = $row['borrower_id'];
    $book_id = $row['book_id'];
    $due_date = $row['due_date'];
    $book_condition = $row['book_condition'];
    $status = $row['status'];

    // Calculate overdue days
    $due_timestamp = strtotime($due_date);
    $today_timestamp = strtotime($today);
    $overdue_days = floor(($today_timestamp - $due_timestamp) / (60 * 60 * 24));

    if ($overdue_days <= 0) {
        continue; // Not overdue
    }

    // Calculate fine
    $daily_fine = 10;
    $fine_amount = $overdue_days * $daily_fine;

    // Check if a penalty already exists for this transaction and date
    $penalty_check_query = "
        SELECT penalty_id, penalty_fee 
        FROM penalties 
        WHERE transaction_id = '$transaction_id' 
            AND penalty_date = '$today'
    ";
    $penalty_result = mysqli_query($conn, $penalty_check_query);
    if (!$penalty_result) {
        error_log("Error checking existing penalties: " . mysqli_error($conn));
        continue;
    }

    if (mysqli_num_rows($penalty_result) > 0) {
        // Update existing penalty_fee by adding $10
        $penalty = mysqli_fetch_assoc($penalty_result);
        $penalty_id = $penalty['penalty_id'];
        $current_fee = $penalty['penalty_fee'];
        $new_fee = $current_fee + $daily_fine;

        $update_penalty_query = "
            UPDATE penalties 
            SET penalty_fee = '$new_fee', overdue_days = overdue_days + 1
            WHERE penalty_id = '$penalty_id'
        ";
        if (!mysqli_query($conn, $update_penalty_query)) {
            error_log("Error updating penalty: " . mysqli_error($conn));
            continue;
        }
    } else {
        // Insert a new penalty record
        $penalty_reason = 'overdue';
        $insert_penalty_query = "
            INSERT INTO penalties 
                (transaction_id, borrower_id, book_id, overdue_days, book_condition, penalty_fee, penalty_reason, penalty_date) 
            VALUES 
                ('$transaction_id', '$borrower_id', '$book_id', '$overdue_days', '$book_condition', '$daily_fine', '$penalty_reason', '$today')
        ";
        if (!mysqli_query($conn, $insert_penalty_query)) {
            error_log("Error inserting penalty: " . mysqli_error($conn));
            continue;
        }
    }

    // Optionally, update the status to 'overdue' if not already
    if ($status !== 'overdue') {
        $update_status_query = "
            UPDATE borrowingtransactions 
            SET status = 'overdue' 
            WHERE transaction_id = '$transaction_id'
        ";
        if (!mysqli_query($conn, $update_status_query)) {
            error_log("Error updating status to overdue: " . mysqli_error($conn));
            continue;
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>