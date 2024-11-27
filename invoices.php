<?php include 'login_first.php' ?>
<?php

include 'config.php';
include 'header.php';

// Function to calculate total penalty
function calculateTotalPenalty($base_fee, $overdue_days) {
    $fine_days = max(0, $overdue_days - 7);
    $dynamic_fine = $fine_days * 20;
    return $base_fee + $dynamic_fine;
}

if (isset($_POST['pay_penalty'])) {
    // Use prepared statements to enhance security
    $penalty_id = $_POST['penalty_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = date('Y-m-d H:i:s');

    // Retrieve penalty and overdue details using prepared statements
    $stmt = mysqli_prepare($conn, "SELECT t.due_date, DATEDIFF(CURRENT_DATE, t.due_date) AS overdue_days, 
                                      p.penalty_fee, p.status, p.penalty_reason 
                               FROM penalties p
                               JOIN borrowingtransactions t ON p.transaction_id = t.transaction_id
                               WHERE p.penalty_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $penalty_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Check if penalty is already paid
        if ($row['status'] == 'paid') {
            $error_message = "This penalty has already been paid.";
        } else {
            $overdue_days = max(0, $row['overdue_days']); // Ensure non-negative days
            $base_penalty_fee = floatval($row['penalty_fee']);
            
            // Calculate total penalty
            $total_penalty_fee = calculateTotalPenalty($base_penalty_fee, $overdue_days);
            
            // Determine penalty reason
            if ($overdue_days > 7) {
                $penalty_reason = "Damage";
            } else {
                $penalty_reason = $row['penalty_reason'];
            }

            // Convert to numeric values for comparison
            $amount_paid = floatval($amount_paid);

            // Validate payment
            if ($amount_paid < $total_penalty_fee) {
                $error_message = "Insufficient payment. The total penalty fee is ₱" . number_format($total_penalty_fee, 2);
            } else {
                // Calculate change
                $change = $amount_paid - $total_penalty_fee;

                // Update penalty record using prepared statements
                $update_stmt = mysqli_prepare($conn, "UPDATE penalties 
                                                     SET amount_paid = ?, 
                                                         payment_date = ?, 
                                                         status = 'paid', 
                                                         penalty_fee = ?, 
                                                         penalty_reason = ?
                                                     WHERE penalty_id = ?");
                mysqli_stmt_bind_param($update_stmt, "ssdsi", $amount_paid, $payment_date, $total_penalty_fee, $penalty_reason, $penalty_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success_message = "Payment recorded successfully. Change: ₱" . number_format($change, 2);
                    header('Location: invoices.php');
                    exit();
                } else {
                    $error_message = "Error updating payment: " . mysqli_error($conn);
                }

                // Close the update statement
                mysqli_stmt_close($update_stmt);
            }
        }
    } else {
        $error_message = "Invalid penalty ID.";
    }

    // Close the initial statement
    mysqli_stmt_close($stmt);
}
?>

<div class="container mt-4">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Penalties and Payments</h2>
        <button class="btn btn-outline-primary" onclick="window.open('generate_penalty_report.php', '_blank');">
        <i class="fas fa-file-alt" style="margin-right: 8px;"></i> Generate Report</button>
    </div>

    <!-- Unpaid Penalties -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Unpaid Penalties</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Overdue Days</th>
                        <th>Total Penalty Amount</th>
                        <th>Penalty Reason</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Retrieve unpaid penalties
                    $query = "SELECT p.*, 
                             CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                             b.title AS book_title,
                             t.due_date,
                             DATEDIFF(CURRENT_DATE, t.due_date) AS overdue_days,
                             (GREATEST(DATEDIFF(CURRENT_DATE, t.due_date) - 7, 0) * 20) AS dynamic_penalty_fee  
                             FROM penalties p
                             JOIN borrowingtransactions t ON p.transaction_id = t.transaction_id
                             JOIN students s ON t.borrower_id = s.student_id
                             JOIN books b ON t.book_id = b.book_id
                             WHERE p.status = 'unpaid'
                             ORDER BY p.penalty_id DESC";
                    $result = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $overdue_days = $row['overdue_days'];
                        $base_penalty_fee = floatval($row['penalty_fee']);
                        $fine_days = max(0, $overdue_days - 7);
                        $dynamic_penalty_fee = $fine_days * 20;

                        // Determine total penalty and reason
                        if ($overdue_days > 7) {
                            $total_penalty_amount = $base_penalty_fee + $dynamic_penalty_fee;
                            $penalty_reason = "Damage";
                        } else {
                            $total_penalty_amount = $base_penalty_fee;
                            $penalty_reason = $row['penalty_reason'];
                        }

                        $penalty_amount_formatted = number_format($total_penalty_amount, 2);

                        echo "<tr>
                                <td>{$row['penalty_id']}</td>
                                <td>{$row['student_name']}</td>
                                <td>{$row['book_title']}</td>
                                <td>{$overdue_days} day(s)</td>
                                <td>₱{$penalty_amount_formatted}</td>
                                <td>{$penalty_reason}</td>
                                <td>" . date('Y-m-d', strtotime($row['due_date'])) . "</td>
                                <td><span class='badge bg-warning'>{$row['status']}</span></td>
                                <td>
                                    <button class='btn btn-sm btn-primary' onclick='showPaymentModal({$row['penalty_id']}, {$total_penalty_amount})'>Record Payment</button>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
    <div class="card-header">
        <h5>Payment History</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="paymentHistoryTable">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Book</th>
                    <th>Penalty Amount</th>
                    <th>Amount Paid</th>
                    <th>Change</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAmountPaid = 0; // Initialize total amount paid

                $query = "SELECT p.*, 
                         CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                         b.title AS book_title
                         FROM penalties p
                         JOIN borrowingtransactions t ON p.transaction_id = t.transaction_id
                         JOIN students s ON t.borrower_id = s.student_id
                         JOIN books b ON t.book_id = b.book_id
                         WHERE p.status = 'paid'
                         ORDER BY p.penalty_id DESC";
                $result = mysqli_query($conn, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    $penalty_amount = number_format($row['penalty_fee'], 2);
                    $amount_paid = number_format($row['amount_paid'], 2);
                    $change = number_format($row['amount_paid'] - $row['penalty_fee'], 2);
                    $totalAmountPaid += $row['amount_paid']; // Add to total amount paid

                    echo "<tr>
                            <td>{$row['penalty_id']}</td>
                            <td>{$row['student_name']}</td>
                            <td>{$row['book_title']}</td>
                            <td>₱{$penalty_amount}</td>
                            <td>₱{$amount_paid}</td>
                            <td>₱{$change}</td>
                            <td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>
                            <td><span class='badge bg-success'>Paid</span></td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="mt-3">
            <strong>Total Amount Paid: </strong>
            <span id="totalAmountPaid">₱<?php echo number_format($totalAmountPaid, 2); ?></span>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="penalty_id" name="penalty_id">
                    <div class="mb-3">
                        <label for="penalty_amount" class="form-label">Total Penalty Fee</label>
                        <input type="text" class="form-control" id="penalty_amount" name="penalty_amount" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" id="amount_paid" name="amount_paid" required min="0" step="0.01">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="pay_penalty">Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showPaymentModal(penaltyId, totalPenaltyFee) {
    // Set penalty ID 
    document.getElementById('penalty_id').value = penaltyId;
    
    // Set penalty amount with correct formatting
    document.getElementById('penalty_amount').value = '₱' + parseFloat(totalPenaltyFee).toFixed(2);
    
    // Use Bootstrap's modal method to show the modal
    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
    
    // Select the form by its method attribute
    var form = document.querySelector('form[method="POST"]');
    
    // Remove any existing event listeners to prevent multiple attachments
    form.removeEventListener('submit', validatePayment);
    
    // Define validation function
    function validatePayment(event) {
        var penaltyAmount = parseFloat(totalPenaltyFee);
        var amountPaid = parseFloat(document.getElementById('amount_paid').value);
        
        // Validate payment amount
        if (amountPaid < penaltyAmount) {
            event.preventDefault(); // Prevent form submission
            alert('Payment must be at least ₱' + penaltyAmount.toFixed(2));
        }
    }
    
    // Add event listener for form submission
    form.addEventListener('submit', validatePayment);
}
</script>

<?php include 'footer.php'; ?>