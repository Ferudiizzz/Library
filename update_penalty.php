<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['penalty_id'])) {
    $penalty_id = $conn->real_escape_string($_POST['penalty_id']);
    $current_date = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update penalty status
        $update_query = "UPDATE penalties 
                        SET status = 'paid',
                            payment_date = ?,
                            amount_paid = penalty_fee
                        WHERE penalty_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $current_date, $penalty_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("No penalty record updated");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

$conn->close();
?>