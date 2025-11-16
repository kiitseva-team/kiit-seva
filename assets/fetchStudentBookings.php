<?php
session_start();
include("config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['id'];

try {
    $db_path = __DIR__ . '/../bookings.db';
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE student_id = ? ORDER BY booking_date DESC, time_slot");
    $stmt->execute([$student_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($bookings as &$booking) {
        $safe_teacher_id = mysqli_real_escape_string($conn, $booking['teacher_id']);
        $sql = "SELECT fname, lname, subject, email FROM teachers WHERE id = '$safe_teacher_id'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $booking['teacher_name'] = $row['fname'] . ' ' . $row['lname'];
            $booking['subject'] = $row['subject'];
            $booking['teacher_email'] = $row['email'];
        }
    }
    
    echo json_encode(['success' => true, 'bookings' => $bookings]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>