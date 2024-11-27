<?php
// File: api/get_overdue.php

require_once 'functions.php';

include 'config.php';

header('Content-Type: application/json');

try {
    // Fetch overdue transactions
    $query = "SELECT 
                a.alert_id,
                a.message,
                a.status,
                a.created_at,
                s.first_name,
                s.last_name,
                s.student_id,
                b.title,
                b.book_id
              FROM alerts a
              JOIN students s ON a.borrower_id = s.student_id
              JOIN books b ON a.book_id = b.book_id
              WHERE a.alert_type = 'overdue' AND a.status = 'unread'
              ORDER BY a.created_at DESC";

    $result = mysqli_query($conn, $query);
    $overdueAlerts = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $overdueAlerts[] = [
            'alert_id'      => $row['alert_id'],
            'message'       => $row['message'],
            'status'        => $row['status'],
            'created_at'    => $row['created_at'],
            'student_name'  => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'student_id'    => $row['student_id'],
            'book_title'    => htmlspecialchars($row['title']),
            'book_id'       => $row['book_id']
        ];
    }

    echo json_encode(['success' => true, 'data' => $overdueAlerts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}