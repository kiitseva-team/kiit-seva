<?php
session_start();
include("config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['id'];

try {
    $db_path = __DIR__ . '/../bookings.db';
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE teacher_id = ? ORDER BY booking_date, time_slot");
    $stmt->execute([$teacher_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($bookings as &$booking) {
        $safe_student_id = mysqli_real_escape_string($conn, $booking['student_id']);
        $sql = "SELECT fname, lname, email, phone, class, section FROM students WHERE id = '$safe_student_id'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $booking['student_name'] = $row['fname'] . ' ' . $row['lname'];
            $booking['student_email'] = $row['email'];
            $booking['student_phone'] = $row['phone'];
            $booking['class'] = $row['class'];
            $booking['section'] = $row['section'];
        }
    }
    
    echo json_encode(['success' => true, 'bookings' => $bookings]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>