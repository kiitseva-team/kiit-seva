<?php
session_start();
include("config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get search query if provided
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Build SQL query with optional search
$sql = "SELECT 
            t.id, 
            t.fname, 
            t.lname, 
            t.subject, 
            t.email, 
            t.phone,
            ts.chamber_no,
            ts.bio,
            ts.available_slots
        FROM teachers t
        LEFT JOIN teacher_slots ts ON t.id = ts.teacher_id";

if (!empty($search)) {
    $sql .= " WHERE (t.fname LIKE '%$search%' 
              OR t.lname LIKE '%$search%' 
              OR t.subject LIKE '%$search%'
              OR CONCAT(t.fname, ' ', t.lname) LIKE '%$search%')";
}

$sql .= " ORDER BY t.fname, t.lname";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit();
}

$teachers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teachers[] = [
        'id' => $row['id'],
        'fname' => $row['fname'],
        'lname' => $row['lname'],
        'subject' => $row['subject'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'chamber_no' => $row['chamber_no'] ?? 'Not set',
        'bio' => $row['bio'] ?? '',
        'available_slots' => $row['available_slots'] ?? '{}'
    ];
}

echo json_encode(['success' => true, 'teachers' => $teachers]);
mysqli_close($conn);
?>
