<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$booking_id = $_POST['booking_id'] ?? '';

if (empty($booking_id)) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

try {
    $db_path = __DIR__ . '/../bookings.db';
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($role == 'student') {
        $check = $pdo->prepare("SELECT s_no FROM bookings WHERE s_no = ? AND student_id = ?");
    } else {
        $check = $pdo->prepare("SELECT s_no FROM bookings WHERE s_no = ? AND teacher_id = ?");
    }
    $check->execute([$booking_id, $user_id]);
    
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or unauthorized']);
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE s_no = ?");
    $stmt->execute([$booking_id]);
    
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>