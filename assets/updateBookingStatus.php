<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$booking_id = $_POST['booking_id'] ?? '';
$status = $_POST['status'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($booking_id) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$teacher_id = $_SESSION['id'];

try {
    $db_path = __DIR__ . '/../bookings.db';
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $check = $pdo->prepare("SELECT s_no FROM bookings WHERE s_no = ? AND teacher_id = ?");
    $check->execute([$booking_id, $teacher_id]);
    
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or unauthorized']);
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE s_no = ?");
    $stmt->execute([$status, $notes, $booking_id]);
    
    echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>