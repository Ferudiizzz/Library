<?php
include 'config.php';

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Fetch borrowed books
    $stmt = $conn->prepare("SELECT 
                                b.title,
                                bt.borrow_date,
                                bt.due_date,
                                bt.status
                             FROM borrowingtransactions bt
                             JOIN books b ON bt.book_id = b.book_id
                             WHERE bt.borrower_id = ? 
                             AND bt.status = 'borrowed'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $borrowedBooksResult = $stmt->get_result();

    $borrowedBooks = [];
    while ($row = $borrowedBooksResult->fetch_assoc()) {
        $borrowedBooks[] = $row;
    }
    $stmt->close();

    $response = [
        'borrowed_books' => $borrowedBooks
    ];

    // Send JSON response
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No student ID provided']);
}
?>