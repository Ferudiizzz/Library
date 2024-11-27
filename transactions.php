<?php
// Include configuration and header files
include 'login_first.php';
include 'config.php';
include 'header.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define fine amounts for each condition
define('FINE_DAMAGED', 50); // Fine for damaged book
define('FINE_LOST', 100);    // Fine for lost book
define('FINE_OVERDUE_PER_DAY', 10); // Fine per overdue day

// Function to sanitize input data
function sanitize_input($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Handle the Borrow Book process
if (isset($_POST['borrow_book'])) {
    $borrower_id = sanitize_input($conn, $_POST['borrower_id']);
    $book_id = sanitize_input($conn, $_POST['book_id']);
    $borrow_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Prepare statement to get borrower details
    $stmt = $conn->prepare("SELECT first_name, middle_name, last_name FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $borrower_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        echo "<div class='alert alert-danger'>Error fetching borrower details.</div>";
        exit();
    }
    $borrower = $result->fetch_assoc();
    if (!$borrower) {
        echo "<div class='alert alert-danger'>Borrower not found.</div>";
        exit();
    }
    $full_name = trim($borrower['first_name'] . ' ' . $borrower['middle_name'] . ' ' . $borrower['last_name']);

    // Prepare statement to check the number of books already borrowed
    $stmt = $conn->prepare("SELECT COUNT(*) as borrowed_count FROM borrowingtransactions WHERE borrower_id = ? AND status IN ('borrowed', 'overdue')");
    $stmt->bind_param("s", $borrower_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        echo "<div class='alert alert-danger'>Error checking borrowed books.</div>";
        exit();
    }
    $row = $result->fetch_assoc();

    // Limit to 3 books
    if ($row['borrowed_count'] >= 3) {
        echo "<div class='alert alert-danger'> $full_name can only borrow up to 3 books at a time.</div>";
    } else {
        // Prepare statement to check book availability
        $stmt = $conn->prepare("SELECT available_copies FROM books WHERE book_id = ? AND available_copies > 0");
        $stmt->bind_param("s", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            echo "<div class='alert alert-danger'>Error checking book availability.</div>";
            exit();
        }
        if ($result->num_rows > 0) {
            // Begin transaction
            $conn->begin_transaction();
            try {
                // Insert borrowing transaction
                $stmt = $conn->prepare("INSERT INTO borrowingtransactions (borrower_id, book_id, borrow_date, due_date, status, book_condition) VALUES (?, ?, ?, ?, 'borrowed', 'good')");
                $stmt->bind_param("ssss", $borrower_id, $book_id, $borrow_date, $due_date);
                if (!$stmt->execute()) {
                    throw new Exception("Error creating borrowing transaction.");
                }

                // Update book availability
                $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?");
                $stmt->bind_param("s", $book_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating book availability.");
                }

                // Commit transaction
                $conn->commit();
                header('Location: transactions.php');
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Book is not available.</div>";
        }
    }
}

// Handle the Return Book process
if (isset($_POST['process_return'])) {
    $transaction_id = sanitize_input($conn, $_POST['transaction_id']);
    $book_condition = sanitize_input($conn, $_POST['book_condition']);
    $action = isset($_POST['action']) ? sanitize_input($conn, $_POST['action']) : '';
    $fine_amount = 0;
    $penalty_reason = '';
    $new_status = '';
    $new_book_condition = '';

    // Prepare statement to get current transaction details
    $stmt = $conn->prepare("SELECT * FROM borrowingtransactions WHERE transaction_id = ?");
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result || $result->num_rows == 0) {
        echo "<div class='alert alert-danger'>Transaction not found.</div>";
        exit();
    }
    $transaction = $result->fetch_assoc();
    $current_status = $transaction['status'];
    $return_date = date('Y-m-d H:i:s');
    $due_date = $transaction['due_date'];
    $overdue_days = max(0, floor((strtotime($return_date) - strtotime($due_date)) / (60 * 60 * 24)));

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Process based on current status
        if ($current_status === 'borrowed' || $current_status === 'overdue') {
            // Handle return from 'borrowed' or 'overdue' status
            if ($book_condition === 'damaged') {
                $fine_amount = FINE_DAMAGED;
                $penalty_reason = 'damage';
                $new_status = 'returned';
                $new_book_condition = 'damaged';
            } elseif ($book_condition === 'lost') {
                if ($action === 'pay') {
                    $fine_amount = FINE_LOST;
                    $penalty_reason = 'loss';
                    $new_status = 'lost';
                    $new_book_condition = 'lost';
                } elseif ($action === 'rebind') {
                    $fine_amount = 0; // No fine yet
                    $penalty_reason = 'rebind';
                    $new_status = 'pending_rebind';
                    $new_book_condition = 'pending';
                } else {
                    // If action is not set, treat as lost without additional action
                    $fine_amount = FINE_LOST;
                    $penalty_reason = 'loss';
                    $new_status = 'lost';
                    $new_book_condition = 'lost';
                }
            } elseif ($book_condition === 'good') {
                if ($overdue_days > 0) {
                    $fine_amount = $overdue_days * FINE_OVERDUE_PER_DAY; // Calculate overdue fine
                    $penalty_reason = 'overdue';
                } else {
                    $fine_amount = 0;
                    $penalty_reason = '';
                }
                $new_status = 'returned';
                $new_book_condition = 'good';
            } else {
                throw new Exception("Invalid book condition.");
            }

            // Update transaction
            $stmt = $conn->prepare("UPDATE borrowingtransactions SET return_date = ?, status = ?, book_condition = ? WHERE transaction_id = ?");
            $stmt->bind_param("ssss", $return_date, $new_status, $new_book_condition, $transaction_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating transaction.");
            }

            // Update book availability if not lost or pending_rebind
            if ($new_status !== 'lost' && $new_status !== 'pending_rebind') {
                $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
                $stmt->bind_param("s", $transaction['book_id']);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating book availability.");
                }
            }

            // Add penalty record if there's a fine
            if ($fine_amount > 0) {
                $stmt = $conn->prepare("INSERT INTO penalties (transaction_id, borrower_id, book_id, overdue_days, book_condition, penalty_fee, penalty_reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisds", $transaction_id, $transaction['borrower_id'], $transaction['book_id'], $overdue_days, $new_book_condition, $fine_amount, $penalty_reason);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting penalty record.");
                }
            }

        } elseif ($current_status === 'pending_rebind') {
            // Handle return from 'pending_rebind' status
            if ($book_condition === 'good') {
                // Assume rebind is completed and book is returned in good condition
                $new_status = 'returned';
                $new_book_condition = 'good';

                // Update transaction
                $stmt = $conn->prepare("UPDATE borrowingtransactions SET return_date = ?, status = ?, book_condition = ? WHERE transaction_id = ?");
                $stmt->bind_param("ssss", $return_date, $new_status, $new_book_condition, $transaction_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating transaction.");
                }

                // Update book availability
                $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
                $stmt->bind_param("s", $transaction['book_id']);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating book availability.");
                }
            } else {
                throw new Exception("Invalid action. To complete rebind, mark the book as returned in good condition.");
            }
        } else {
            throw new Exception("Invalid transaction status.");
        }

        // Commit transaction
        $conn->commit();
        header('Location: transactions.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    }
}

// Handle the Delete Transaction process
if (isset($_POST['delete_transaction'])) {
    $transaction_id = sanitize_input($conn, $_POST['transaction_id']);

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Delete related penalties
        $stmt = $conn->prepare("DELETE FROM penalties WHERE transaction_id = ?");
        $stmt->bind_param("s", $transaction_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting penalties.");
        }

        // Get book_id before deleting the transaction
        $stmt = $conn->prepare("SELECT book_id FROM borrowingtransactions WHERE transaction_id = ?");
        $stmt->bind_param("s", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            $book_id = $transaction['book_id'];
        } else {
            throw new Exception("Transaction not found.");
        }

        // Delete the transaction
        $stmt = $conn->prepare("DELETE FROM borrowingtransactions WHERE transaction_id = ?");
        $stmt->bind_param("s", $transaction_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting transaction.");
        }

        // Update book availability
        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
        $stmt->bind_param("s", $book_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating book availability.");
        }

        // Commit transaction
        $conn->commit();
        header('Location: transactions.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    }
}
?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Library Transactions</h2>
            <!-- Button to trigger New Borrowing Modal -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBorrowingModal">New Borrowing</button>
        </div>

        <!-- New Borrowing Modal -->
        <div class="modal fade" id="newBorrowingModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Borrowing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Student</label>
                                <select class="form-select" name="borrower_id" required>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM students ORDER BY last_name, first_name");
                                    $stmt->execute();
                                    $students = $stmt->get_result();
                                    if ($students) {
                                        while ($student = $students->fetch_assoc()) {
                                            $student_id = htmlspecialchars($student['student_id']);
                                            $student_name = htmlspecialchars("{$student['last_name']}, {$student['first_name']}");
                                            echo "<option value='{$student_id}'>{$student_name}</option>";
                                        }
                                    } else {
                                        echo "<option disabled>No students available</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Book</label>
                                <select class="form-select" name="book_id" required>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM books WHERE available_copies > 0 ORDER BY title ASC");
                                    $stmt->execute();
                                    $books = $stmt->get_result();
                                    if ($books) {
                                        while ($book = $books->fetch_assoc()) {
                                            $book_id = htmlspecialchars($book['book_id']);
                                            $book_title = htmlspecialchars("{$book['title']} ({$book['available_copies']} available)");
                                            echo "<option value='{$book_id}'>{$book_title}</option>";
                                        }
                                    } else {
                                        echo "<option disabled>No books available</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="borrow_book" class="btn btn-primary">Create Borrowing</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Return Book Modal -->
        <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Return Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="transaction_id" id="transaction_id">
                            <input type="hidden" name="action" id="action" value="">

                            <div class="mb-3" id="bookConditionDiv">
                                <label class="form-label">Condition of the Book</label>
                                <select class="form-select" name="book_condition" id="book_condition" required onchange="checkBookCondition()">
                                    <option value="good">Returned in Good Condition</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>

                            <!-- Lost Book Actions (Initially Hidden) -->
                            <div id="lostBookOptions" style="display: none;">
                                <p>Please select one of the following actions:</p>
                                <button type="button" class="btn btn-warning me-2" onclick="payForBook()">Pay for the Book</button>
                                <button type="button" class="btn btn-warning" onclick="rebindBook()">Rebind the Book</button>
                            </div>

                            <!-- Rebind Confirmation and Status (Initially Hidden) -->
                            <div id="rebindConfirmation" style="display: none;">
                                <p>The book will be marked as "Pending Rebind" until the rebind process is completed. Are you sure?</p>
                            </div>

                            <!-- Confirmation for return (Initially Hidden) -->
                            <div id="returnConfirmation" style="display: none;">
                                <p>The book will be marked as lost. Are you sure?</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="process_return" class="btn btn-primary" id="returnButton" disabled>Return</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">Borrowing Transactions</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Prepare statement to fetch transactions
                            $stmt = $conn->prepare("
                                SELECT t.*, s.first_name, s.last_name, b.title as book_title 
                                FROM borrowingtransactions t 
                                JOIN students s ON t.borrower_id = s.student_id 
                                JOIN books b ON t.book_id = b.book_id 
                                ORDER BY t.borrow_date DESC
                            ");
                            $stmt->execute();
                            $transactions = $stmt->get_result();
                            while ($row = $transactions->fetch_assoc()) {
                                // Format the status display
                                $statusClass = '';
                                $statusDisplay = '';

                                switch ($row['status']) {
                                    case 'borrowed':
                                        $statusClass = 'text-primary';
                                        $statusDisplay = 'Borrowed';
                                        break;
                                    case 'overdue':
                                        $statusClass = 'text-warning';
                                        $statusDisplay = 'Overdue';
                                        break;
                                    case 'returned':
                                        $statusClass = 'text-success';
                                        $statusDisplay = 'Returned';
                                        break;
                                    case 'pending_rebind':
                                        $statusClass = 'text-secondary';
                                        $statusDisplay = 'Pending Rebind';
                                        break;
                                    case 'lost':
                                        $statusClass = 'text-danger';
                                        $statusDisplay = 'Lost';
                                        break;
                                    default:
                                        $statusClass = 'text-secondary';
                                        $statusDisplay = ucfirst(str_replace('_', ' ', $row['status']));
                                }

                                // Prepare student name
                                $student_name = htmlspecialchars("{$row['first_name']} {$row['last_name']}");
                                $book_title = htmlspecialchars($row['book_title']);
                                $borrow_date = htmlspecialchars(date('Y-m-d', strtotime($row['borrow_date'])));
                                $due_date_display = htmlspecialchars(date('Y-m-d', strtotime($row['due_date'])));
                                $return_date_display = $row['return_date'] ? htmlspecialchars(date('Y-m-d', strtotime($row['return_date']))) : '-';
                                $transaction_id = htmlspecialchars($row['transaction_id']);
                                $status_display_safe = htmlspecialchars($statusDisplay);

                                echo "<tr>";
                                echo "<td>{$transaction_id}</td>";
                                echo "<td>{$student_name}</td>";
                                echo "<td>{$book_title}</td>";
                                echo "<td>{$borrow_date}</td>";
                                echo "<td>{$due_date_display}</td>";
                                echo "<td>{$return_date_display}</td>";
                                echo "<td class='{$statusClass}'><strong>{$status_display_safe}</strong></td>";
                                echo "<td>";
                                // Return Button - show for 'borrowed', 'overdue', and 'pending_rebind' statuses
                                if (in_array($row['status'], ['borrowed', 'overdue', 'pending_rebind'])) {
                                    echo "<button class='btn btn-success btn-sm me-2' 
                                                 data-bs-toggle='modal' 
                                                 data-bs-target='#returnModal' 
                                                 data-transaction_id='{$transaction_id}'
                                                 data-status='{$row['status']}'>
                                          Return
                                      </button>";
                                }
                                // Delete Button
                                echo "<form method='POST' style='display:inline-block;'>
                                        <input type='hidden' name='transaction_id' value='{$transaction_id}'>
                                        <button type='submit' name='delete_transaction' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this transaction?');\">Delete</button>
                                      </form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Include the footer
    include 'footer.php';
    ?>

 
<script>
    // Wait for the DOM to load
    document.addEventListener('DOMContentLoaded', function () {
        // Populate return modal with transaction_id and adjust based on status
        var returnButtons = document.querySelectorAll('[data-bs-target="#returnModal"]');
        returnButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var transactionId = this.getAttribute('data-transaction_id');
                var status = this.getAttribute('data-status');

                document.getElementById('transaction_id').value = transactionId;
                document.getElementById('action').value = ''; // Reset action

                // Reset the modal state
                document.getElementById("lostBookOptions").style.display = "none";
                document.getElementById("rebindConfirmation").style.display = "none";
                document.getElementById("returnConfirmation").style.display = "none";

                if (status === 'pending_rebind') {
                    // Hide condition selection for pending_rebind
                    document.getElementById("book_condition").value = 'good'; // Assume rebind results in good condition
                    document.getElementById("bookConditionDiv").style.display = "none"; // Hide condition selection
                    document.getElementById("returnButton").disabled = false; // Enable return button
                } else {
                    // Show condition selection for borrowed and overdue statuses
                    document.getElementById("book_condition").value = 'good'; // Reset to default
                    document.getElementById("bookConditionDiv").style.display = "block"; // Show condition selection
                    document.getElementById("returnButton").disabled = true; // Disable until a valid condition is selected
                }
            });
        });

        // Enable the Return button only when a valid action is selected
        document.getElementById('returnModal').addEventListener('change', function (e) {
            if (e.target && e.target.id === 'book_condition') {
                checkBookCondition();
            }
        });
    });

    // Show options when "Lost" is selected
    function checkBookCondition() {
        const condition = document.getElementById("book_condition").value;
        const lostBookOptions = document.getElementById("lostBookOptions");
        const returnButton = document.getElementById("returnButton");
        const actionField = document.getElementById("action");
        const returnConfirmation = document.getElementById("returnConfirmation");
        const rebindConfirmation = document.getElementById("rebindConfirmation");

        if (condition === "lost") {
            lostBookOptions.style.display = "block"; // Show rebind and pay buttons
            returnButton.disabled = true; // Disable return button until action is chosen
        } else {
            lostBookOptions.style.display = "none"; // Hide rebind and pay buttons
            returnButton.disabled = false; // Enable return button
            actionField.value = ''; // Reset action
            returnConfirmation.style.display = "none"; // Hide lost confirmation
            rebindConfirmation.style.display = "none"; // Hide rebind confirmation
        }
    }

    // Action for paying for the book
    function payForBook() {
        if (confirm("You chose to pay for the book. Proceed with the payment?")) {
            document.getElementById("action").value = 'pay'; // Set action to 'pay'
            document.getElementById("returnButton").disabled = false; // Enable return button
            document.getElementById("returnConfirmation").style.display = "block"; // Show lost confirmation
            document.getElementById("rebindConfirmation").style.display = "none"; // Hide rebind confirmation
        }
    }

    // Action for rebinding the book
    function rebindBook() {
        if (confirm("You chose to rebind the book. Proceed with the rebind?")) {
            document.getElementById("action").value = 'rebind'; // Set action to 'rebind'
            document.getElementById("returnButton").disabled = false; // Enable return button
            document.getElementById("rebindConfirmation").style.display = "block"; // Show rebind confirmation
            document.getElementById("returnConfirmation").style.display = "none"; // Hide lost confirmation
        }
    }
</script>
