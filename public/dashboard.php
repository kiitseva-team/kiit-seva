<?php
$pageTitle = 'Dashboard';
require_once '../includes/header.php';
require_login();

$user = $auth->getCurrentUser();
$role = getUserRole();

// Get user statistics based on role
$stats = [];

try {
    if ($role === 'student') {
        // Get booking statistics
        $db->query('SELECT COUNT(*) as total_bookings FROM bookings WHERE student_id = :user_id');
        $db->bind(':user_id', getUserId());
        $stats['bookings'] = $db->single()->total_bookings;

        $db->query('SELECT COUNT(*) as pending_bookings FROM bookings WHERE student_id = :user_id AND status = "pending"');
        $db->bind(':user_id', getUserId());
        $stats['pending_bookings'] = $db->single()->pending_bookings;

        $db->query('SELECT COUNT(*) as feedback_count FROM feedback WHERE user_id = :user_id');
        $db->bind(':user_id', getUserId());
        $stats['feedback_count'] = $db->single()->feedback_count;

        // Get recent bookings
        $db->query('SELECT b.*, t.user_id, u.full_name as teacher_name, b.meeting_date, b.meeting_time 
                   FROM bookings b 
                   JOIN teachers t ON b.teacher_id = t.id 
                   JOIN users u ON t.user_id = u.id 
                   WHERE b.student_id = :user_id 
                   ORDER BY b.booking_date DESC LIMIT 5');
        $db->bind(':user_id', getUserId());
        $recent_bookings = $db->resultSet();

    } elseif ($role === 'teacher') {
        // Get teacher's ID
        $db->query('SELECT id FROM teachers WHERE user_id = :user_id');
        $db->bind(':user_id', getUserId());
        $teacher_data = $db->single();
        $teacher_id = $teacher_data ? $teacher_data->id : 0;

        $db->query('SELECT COUNT(*) as total_bookings FROM bookings WHERE teacher_id = :teacher_id');
        $db->bind(':teacher_id', $teacher_id);
        $stats['bookings'] = $db->single()->total_bookings;

        $db->query('SELECT COUNT(*) as pending_bookings FROM bookings WHERE teacher_id = :teacher_id AND status = "pending"');
        $db->bind(':teacher_id', $teacher_id);
        $stats['pending_bookings'] = $db->single()->pending_bookings;

        $db->query('SELECT COUNT(*) as available_slots FROM time_slots WHERE teacher_id = :teacher_id AND is_available = 1 AND date >= CURDATE()');
        $db->bind(':teacher_id', $teacher_id);
        $stats['available_slots'] = $db->single()->available_slots;

        // Get recent bookings for teacher
        $db->query('SELECT b.*, u.full_name as student_name, u.email as student_email 
                   FROM bookings b 
                   JOIN users u ON b.student_id = u.id 
                   WHERE b.teacher_id = :teacher_id 
                   ORDER BY b.booking_date DESC LIMIT 5');
        $db->bind(':teacher_id', $teacher_id);
        $recent_bookings = $db->resultSet();

    } elseif ($role === 'admin' || $role === 'staff') {
        // Get overall statistics
        $db->query('SELECT COUNT(*) as total_users FROM users WHERE status = "active"');
        $stats['total_users'] = $db->single()->total_users;

        $db->query('SELECT COUNT(*) as total_bookings FROM bookings');
        $stats['total_bookings'] = $db->single()->total_bookings;

        $db->query('SELECT COUNT(*) as active_vehicles FROM vehicles WHERE status = "active"');
        $stats['active_vehicles'] = $db->single()->active_vehicles;

        $db->query('SELECT COUNT(*) as pending_feedback FROM feedback WHERE status = "pending"');
        $stats['pending_feedback'] = $db->single()->pending_feedback;

        // Get recent feedback
        $db->query('SELECT f.*, u.full_name as user_name 
                   FROM feedback f 
                   LEFT JOIN users u ON f.user_id = u.id 
                   ORDER BY f.created_at DESC LIMIT 5');
        $recent_feedback = $db->resultSet();
    }
} catch (Exception $e) {
    // Handle database errors gracefully
    $stats = [];
    $recent_bookings = [];
    $recent_feedback = [];
}
?>

<section class="section">
    <div class="container">
        <!-- Welcome Header -->
        <div class="hero is-info is-small">
            <div class="hero-body">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <div>
                                <h1 class="title is-3">
                                    <i class="mdi mdi-view-dashboard"></i> 
                                    Welcome back, <?php echo getUserName(); ?>!
                                </h1>
                                <p class="subtitle is-5">
                                    Role: <span class="tag is-white"><?php echo ucfirst($role); ?></span>
                                    <?php if ($user->department): ?>
                                        | Department: <span class="tag is-white"><?php echo $user->department; ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <p class="heading">Last Login</p>
                            <p class="title is-6"><?php echo date('M j, Y g:i A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="columns is-multiline mt-5">
            <?php if ($role === 'student'): ?>
                <div class="column is-one-third">
                    <div class="box has-background-primary">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Total Bookings</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['bookings'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-calendar-check is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-third">
                    <div class="box has-background-warning">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered">
                                    <p class="heading">Pending Bookings</p>
                                    <p class="title is-3"><?php echo $stats['pending_bookings'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-clock-outline is-size-1"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-third">
                    <div class="box has-background-success">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Feedback Submitted</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['feedback_count'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-comment-text is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role === 'teacher'): ?>
                <div class="column is-one-third">
                    <div class="box has-background-primary">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Total Bookings</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['bookings'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-calendar-multiple is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-third">
                    <div class="box has-background-warning">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered">
                                    <p class="heading">Pending Approval</p>
                                    <p class="title is-3"><?php echo $stats['pending_bookings'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-account-clock is-size-1"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-third">
                    <div class="box has-background-info">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Available Slots</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['available_slots'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-calendar-plus is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: // Admin/Staff ?>
                <div class="column is-one-quarter">
                    <div class="box has-background-primary">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Total Users</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['total_users'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-account-group is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-quarter">
                    <div class="box has-background-info">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Total Bookings</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['total_bookings'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-calendar-check is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-quarter">
                    <div class="box has-background-success">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered has-text-white">
                                    <p class="heading">Active Vehicles</p>
                                    <p class="title is-3 has-text-white"><?php echo $stats['active_vehicles'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-car is-size-1 has-text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-one-quarter">
                    <div class="box has-background-warning">
                        <div class="level">
                            <div class="level-item">
                                <div class="has-text-centered">
                                    <p class="heading">Pending Feedback</p>
                                    <p class="title is-3"><?php echo $stats['pending_feedback'] ?? 0; ?></p>
                                </div>
                            </div>
                            <div class="level-item">
                                <i class="mdi mdi-message-alert is-size-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="columns mt-5">
            <div class="column is-one-third">
                <div class="box">
                    <h4 class="title is-5">
                        <i class="mdi mdi-lightning-bolt has-text-warning"></i> Quick Actions
                    </h4>
                    <div class="buttons">
                        <?php if ($role === 'student'): ?>
                            <a href="booking.php" class="button is-primary">
                                <i class="mdi mdi-calendar-plus"></i>&nbsp; Book Teacher
                            </a>
                            <a href="vehicles.php" class="button is-info">
                                <i class="mdi mdi-map-marker"></i>&nbsp; Track Vehicles
                            </a>
                            <a href="feedback.php" class="button is-success">
                                <i class="mdi mdi-comment-plus"></i>&nbsp; Give Feedback
                            </a>
                        <?php elseif ($role === 'teacher'): ?>
                            <a href="booking.php" class="button is-primary">
                                <i class="mdi mdi-calendar-clock"></i>&nbsp; Manage Bookings
                            </a>
                            <a href="vehicles.php" class="button is-info">
                                <i class="mdi mdi-bus"></i>&nbsp; Vehicle Info
                            </a>
                            <a href="feedback.php" class="button is-success">
                                <i class="mdi mdi-comment-text"></i>&nbsp; Submit Feedback
                            </a>
                        <?php else: ?>
                            <a href="../admin/teachers.php" class="button is-primary">
                                <i class="mdi mdi-account-tie"></i>&nbsp; Manage Teachers
                            </a>
                            <a href="../admin/vehicles.php" class="button is-info">
                                <i class="mdi mdi-car-multiple"></i>&nbsp; Manage Vehicles
                            </a>
                            <a href="../admin/feedbacks.php" class="button is-success">
                                <i class="mdi mdi-message-text-outline"></i>&nbsp; View Feedback
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="column is-two-thirds">
                <div class="box">
                    <h4 class="title is-5">
                        <i class="mdi mdi-history has-text-info"></i> Recent Activity
                    </h4>

                    <?php if (($role === 'student' || $role === 'teacher') && !empty($recent_bookings)): ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <?php if ($role === 'student'): ?>
                                            <th>Teacher</th>
                                            <th>Meeting Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        <?php else: ?>
                                            <th>Student</th>
                                            <th>Meeting Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars($role === 'student' ? 
                                                    $booking->teacher_name : 
                                                    $booking->student_name); 
                                                ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($booking->meeting_date)); ?></td>
                                            <td><?php echo date('g:i A', strtotime($booking->meeting_time)); ?></td>
                                            <td>
                                                <span class="tag booking-status <?php echo $booking->status; ?>">
                                                    <?php echo ucfirst($booking->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif (($role === 'admin' || $role === 'staff') && !empty($recent_feedback)): ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Category</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_feedback as $feedback): ?>
                                        <tr>
                                            <td><?php echo $feedback->is_anonymous ? 'Anonymous' : htmlspecialchars($feedback->user_name); ?></td>
                                            <td><span class="tag"><?php echo ucfirst($feedback->category); ?></span></td>
                                            <td><?php echo htmlspecialchars($feedback->subject); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($feedback->created_at)); ?></td>
                                            <td>
                                                <span class="tag is-<?php echo $feedback->status === 'pending' ? 'warning' : ($feedback->status === 'resolved' ? 'success' : 'info'); ?>">
                                                    <?php echo ucfirst($feedback->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="has-text-centered py-6">
                            <i class="mdi mdi-information is-size-1 has-text-grey-light"></i>
                            <p class="has-text-grey">No recent activity to display.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Announcements -->
        <div class="box mt-5">
            <h4 class="title is-5">
                <i class="mdi mdi-bullhorn has-text-danger"></i> System Announcements
            </h4>
            <div class="content">
                <div class="notification is-info is-light">
                    <strong>System Maintenance:</strong> Scheduled maintenance on Sunday, 2:00 AM - 4:00 AM. Some services may be temporarily unavailable.
                </div>
                <div class="notification is-success is-light">
                    <strong>New Feature:</strong> Real-time vehicle tracking is now available! Check the Vehicle Tracking page for live updates.
                </div>
                <div class="notification is-warning is-light">
                    <strong>Reminder:</strong> Please update your profile information and contact details to ensure smooth communication.
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>