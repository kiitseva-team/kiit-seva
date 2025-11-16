<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include("config.php");

$student_id = $_SESSION['id'];
$teacher_id = $_POST['teacher_id'] ?? '';
$booking_date = $_POST['booking_date'] ?? '';
$time_slot = $_POST['time_slot'] ?? '';
$purpose = $_POST['purpose'] ?? '';

if (empty($teacher_id) || empty($booking_date) || empty($time_slot) || empty($purpose)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$safe_teacher_id = mysqli_real_escape_string($conn, $teacher_id);
$verify_sql = "SELECT id FROM teachers WHERE id = '$safe_teacher_id'";
$verify_result = mysqli_query($conn, $verify_sql);

if (!$verify_result || mysqli_num_rows($verify_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid teacher selected']);
    mysqli_close($conn);
    exit();
}

try {
    $db_path = __DIR__ . '/../bookings.db';
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        s_no INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id VARCHAR(40) NOT NULL,
        teacher_id VARCHAR(40) NOT NULL,
        booking_date DATE NOT NULL,
        time_slot VARCHAR(50) NOT NULL,
        purpose TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        notes VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $check = $pdo->prepare("SELECT s_no FROM bookings 
                           WHERE teacher_id = ? AND booking_date = ? AND time_slot = ? 
                           AND status != 'cancelled'");
    $check->execute([$teacher_id, $booking_date, $time_slot]);
    
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This time slot is already booked']);
        exit();
    }
    
    $stmt = $pdo->prepare("INSERT INTO bookings (student_id, teacher_id, booking_date, time_slot, purpose, status) 
                          VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$student_id, $teacher_id, $booking_date, $time_slot, $purpose]);
    
    echo json_encode(['success' => true, 'message' => 'Booking request submitted successfully!']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>