<?php
require_once dirname(__DIR__) . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'KIIT SEVA'; ?> - Campus Management System</title>
    <link rel="stylesheet" href="../assets/css/bulma.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css">
    <style>
        .navbar-brand .title {
            color: #3273dc !important;
        }
        .hero.is-primary {
            background: linear-gradient(45deg, #3273dc, #48c774);
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .notification.is-primary {
            background-color: #3273dc;
        }
        .footer {
            background-color: #363636;
            color: white;
        }
        .map-container {
            height: 400px;
            border-radius: 6px;
            overflow: hidden;
        }
        .vehicle-status.active {
            color: #48c774;
        }
        .vehicle-status.inactive {
            color: #ff3860;
        }
        .vehicle-status.maintenance {
            color: #ffdd57;
        }
        .booking-status.pending {
            background-color: #ffdd57;
        }
        .booking-status.confirmed {
            background-color: #48c774;
        }
        .booking-status.completed {
            background-color: #3273dc;
        }
        .booking-status.cancelled {
            background-color: #ff3860;
        }
        .time-slot {
            cursor: pointer;
            transition: all 0.2s;
        }
        .time-slot:hover {
            background-color: #f5f5f5;
        }
        .time-slot.selected {
            background-color: #3273dc;
            color: white;
        }
        .time-slot.unavailable {
            background-color: #f5f5f5;
            color: #7a7a7a;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="<?php echo isLoggedIn() ? 'dashboard.php' : 'index.php'; ?>">
                <span class="title is-4 has-text-white">
                    <i class="mdi mdi-school"></i> KIIT SEVA
                </span>
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                <?php if (isLoggedIn()): ?>
                    <a class="navbar-item" href="dashboard.php">
                        <i class="mdi mdi-view-dashboard"></i>&nbsp; Dashboard
                    </a>
                    
                    <?php if (getUserRole() == 'student' || getUserRole() == 'admin'): ?>
                        <a class="navbar-item" href="booking.php">
                            <i class="mdi mdi-calendar-clock"></i>&nbsp; Teacher Booking
                        </a>
                    <?php endif; ?>
                    
                    <a class="navbar-item" href="vehicles.php">
                        <i class="mdi mdi-bus"></i>&nbsp; Vehicle Tracking
                    </a>
                    
                    <a class="navbar-item" href="feedback.php">
                        <i class="mdi mdi-comment-text"></i>&nbsp; Feedback
                    </a>
                    
                    <?php if (getUserRole() == 'admin'): ?>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <i class="mdi mdi-cog"></i>&nbsp; Admin
                            </a>
                            <div class="navbar-dropdown">
                                <a class="navbar-item" href="../admin/teachers.php">
                                    <i class="mdi mdi-account-tie"></i>&nbsp; Manage Teachers
                                </a>
                                <a class="navbar-item" href="../admin/vehicles.php">
                                    <i class="mdi mdi-car"></i>&nbsp; Manage Vehicles
                                </a>
                                <a class="navbar-item" href="../admin/feedbacks.php">
                                    <i class="mdi mdi-message-text"></i>&nbsp; View Feedback
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="navbar-item" href="index.php">
                        <i class="mdi mdi-home"></i>&nbsp; Home
                    </a>
                <?php endif; ?>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown is-hoverable">
                                <div class="dropdown-trigger">
                                    <button class="button is-primary is-outlined" aria-haspopup="true" aria-controls="dropdown-menu">
                                        <i class="mdi mdi-account"></i>&nbsp;
                                        <span><?php echo getUserName(); ?></span>
                                        <span class="icon is-small">
                                            <i class="mdi mdi-chevron-down"></i>
                                        </span>
                                    </button>
                                </div>
                                <div class="dropdown-menu" id="dropdown-menu" role="menu">
                                    <div class="dropdown-content">
                                        <div class="dropdown-item">
                                            <strong><?php echo getUserName(); ?></strong><br>
                                            <small class="has-text-grey"><?php echo ucfirst(getUserRole()); ?></small>
                                        </div>
                                        <hr class="dropdown-divider">
                                        <a href="logout.php" class="dropdown-item">
                                            <i class="mdi mdi-logout"></i>&nbsp; Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a class="button is-primary is-outlined" href="login.php">
                                <i class="mdi mdi-login"></i>&nbsp; Login
                            </a>
                            <a class="button is-primary" href="register.php">
                                <i class="mdi mdi-account-plus"></i>&nbsp; Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main>
        <?php
        // Display flash messages
        if (isset($_GET['success'])) {
            echo '<div class="notification is-success is-light">
                    <button class="delete"></button>
                    ' . htmlspecialchars($_GET['success']) . '
                  </div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="notification is-danger is-light">
                    <button class="delete"></button>
                    ' . htmlspecialchars($_GET['error']) . '
                  </div>';
        }
        if (isset($_GET['info'])) {
            echo '<div class="notification is-info is-light">
                    <button class="delete"></button>
                    ' . htmlspecialchars($_GET['info']) . '
                  </div>';
        }
        ?>

    <script>
        // Mobile navbar burger
        document.addEventListener('DOMContentLoaded', () => {
            const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            if ($navbarBurgers.length > 0) {
                $navbarBurgers.forEach(el => {
                    el.addEventListener('click', () => {
                        const target = el.dataset.target;
                        const $target = document.getElementById(target);
                        el.classList.toggle('is-active');
                        $target.classList.toggle('is-active');
                    });
                });
            }

            // Delete notifications
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>