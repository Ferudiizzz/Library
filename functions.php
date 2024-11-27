<?php


function generateAlerts($conn) {
    // Check if alerts were already generated today
    $today = date('Y-m-d');
    $check_query = "SELECT COUNT(*) as count FROM alerts 
                   WHERE DATE(created_at) = '$today'";
    $result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($result);
    
    // Only generate alerts if none exist for today
    if ($row['count'] == 0) {
        checkOverdueBooks($conn);
        checkStudentStatuses($conn);
    }
}

function checkOverdueBooks($conn): void {
    // Get all overdue books with detailed information
    $query = "SELECT 
        t.transaction_id,
        b.title,
        b.book_id,
        s.student_id,
        s.first_name,
        s.last_name,
        s.contact_number,
        t.borrow_date,
        t.due_date
        FROM borrowingtransactions t
        JOIN books b ON t.book_id = b.book_id
        JOIN students s ON t.borrower_id = s.student_id
        WHERE t.status = 'borrowed'
        AND t.due_date < CURRENT_DATE()
        AND t.return_date IS NULL";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate days overdue
        $due_date = new DateTime($row['due_date']);
        $today = new DateTime();
        $days_overdue = $today->diff($due_date)->days;

        // Create detailed alert message
        $message = sprintf(
            "<strong>Book Details:</strong> '%s' (ID: %s)<br>
            <strong>Borrower:</strong> %s %s (ID: %s)<br>
            <strong>Contact:</strong> %s<br>
            <strong>Borrowed Date:</strong> %s<br>
            <strong>Due Date:</strong> %s<br>
            <strong>Days Overdue:</strong> %d days",
            $row['title'],
            $row['book_id'],
            $row['first_name'],
            $row['last_name'],
            $row['student_id'],
            $row['contact_number'],
            date('Y-m-d', strtotime($row['borrow_date'])),
            date('Y-m-d', strtotime($row['due_date'])),
            $days_overdue
        );

        // Insert alert if it doesn't exist
        $check_query = "SELECT alert_id FROM alerts 
                       WHERE transaction_id = '{$row['transaction_id']}' 
                       AND status = 'unread' 
                       AND alert_type = 'overdue'";
        
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) == 0) {
            $insert_query = "INSERT INTO alerts (
                alert_type,
                message,
                transaction_id,
                status,
                priority,
                borrower_id,
                book_id
            ) VALUES (
                'overdue',
                '" . mysqli_real_escape_string($conn, $message) . "',
                '{$row['transaction_id']}',
                'unread',
                'high',
                '{$row['student_id']}',
                '{$row['book_id']}'
            )";
            mysqli_query($conn, $insert_query);
        }
    }
}
function calculateOverdueDays($due_date) {
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due);
    return ($today > $due) ? $diff->days : 0;
}

function updateTransactionStatus($conn, $transaction_id) {
    $query = "SELECT due_date FROM borrowingtransactions WHERE transaction_id = '$transaction_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    $overdue_days = calculateOverdueDays($row['due_date']);
    if ($overdue_days > 0) {
        mysqli_query($conn, "UPDATE borrowingtransactions SET status = 'overdue' WHERE transaction_id = '$transaction_id'");
    }
}

function checkStudentStatuses($conn) {
    // Check for expired memberships
    $query = "SELECT 
        student_id,
        first_name,
        last_name,
        membership_status,
        membership_expiry,
        membership_type,
        current_borrowed,
        total_penalties
        FROM students
        WHERE 
        (membership_expiry <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY) AND membership_status = 'active')
        OR (current_borrowed >= max_books)
        OR (total_penalties > 50)";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $alert_type = '';
        $priority = 'medium';
        $message = '';

        // Check membership expiry
        if ($row['membership_expiry'] <= date('Y-m-d', strtotime('+7 days'))) {
            $alert_type = 'membership_expiring';
            $days_left = floor((strtotime($row['membership_expiry']) - time()) / (60 * 60 * 24));
            $message = sprintf(
                "<strong>Membership Expiring:</strong><br>
                Student: %s %s (ID: %s)<br>
                Membership Type: %s<br>
                Days Until Expiry: %d<br>
                Current Status: %s",
                $row['first_name'],
                $row['last_name'],
                $row['student_id'],
                ucfirst($row['membership_type']),
                $days_left,
                ucfirst($row['membership_status'])
            );
        }

        // Check borrowed books limit
        if ($row['current_borrowed'] >= 3) {
            $alert_type = 'max_books_reached';
            $message = sprintf(
                "<strong>Maximum Books Limit Reached:</strong><br>
                Student: %s %s (ID: %s)<br>
                Books Borrowed: %d<br>
                Maximum Allowed: %d",
                $row['first_name'],
                $row['last_name'],
                $row['student_id'],
                $row['current_borrowed'],
                3
            );
        }

        // Check penalties
        if ($row['total_penalties'] > 50) {
            $alert_type = 'high_penalties';
            $priority = 'high';
            $message = sprintf(
                "<strong>High Penalties Alert:</strong><br>
                Student: %s %s (ID: %s)<br>
                Total Penalties: $%.2f<br>
                Membership Status: %s",
                $row['first_name'],
                $row['last_name'],
                $row['student_id'],
                $row['total_penalties'],
                ucfirst($row['membership_status'])
            );
        }

        if ($message) {
            // Insert alert if it doesn't exist
            $check_query = "SELECT alert_id FROM alerts 
                           WHERE borrower_id = '{$row['student_id']}' 
                           AND alert_type = '$alert_type'
                           AND status = 'unread'";
            
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) == 0) {
                $insert_query = "INSERT INTO alerts (
                    alert_type,
                    message,
                    borrower_id,
                    status,
                    priority
                ) VALUES (
                    '$alert_type',
                    '" . mysqli_real_escape_string($conn, $message) . "',
                    '{$row['student_id']}',
                    'unread',
                    '$priority'
                )";
                mysqli_query($conn, $insert_query);
            }
        }
    }
}

// Add any other library-related functions below
?>