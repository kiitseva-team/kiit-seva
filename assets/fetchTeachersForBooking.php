<?php
session_start();
include("config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$sql = "SELECT t.id, t.fname, t.lname, t.subject, t.email, t.phone
        FROM teachers t
        ORDER BY t.fname, t.lname";

$result = mysqli_query($conn, $sql);

$teachers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teachers[] = $row;
}

$db_path = __DIR__ . '/../bookings.db';
$pdo = new PDO("sqlite:$db_path");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach ($teachers as &$teacher) {
    $stmt = $pdo->prepare("SELECT chamber_no, available_slots, bio FROM teacher_slots WHERE teacher_id = ?");
    $stmt->execute([$teacher['id']]);
    $slot_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $teacher['chamber_no'] = $slot_data['chamber_no'] ?? null;
    $teacher['available_slots'] = $slot_data['available_slots'] ?? '{}';
    $teacher['bio'] = $slot_data['bio'] ?? null;
}

echo json_encode(['success' => true, 'teachers' => $teachers]);
mysqli_close($conn);
?>