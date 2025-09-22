<?php
$pageTitle = '404 - Page Not Found';
require_once '../includes/header.php';
?>

<section class="hero is-danger is-medium">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title is-1">
                <i class="mdi mdi-alert-circle-outline"></i> 404
            </h1>
            <h2 class="subtitle is-3">
                Page Not Found
            </h2>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-half">
                <div class="box has-text-centered">
                    <figure class="image is-128x128 is-inline-block mb-5">
                        <i class="mdi mdi-file-question is-size-1 has-text-grey-light" style="font-size: 8rem;"></i>
                    </figure>
                    
                    <h3 class="title is-3">Oops! Page Not Found</h3>
                    <p class="subtitle is-5 mb-5">
                        The page you're looking for doesn't exist or has been moved.
                    </p>
                    
                    <div class="content">
                        <p>Here are some helpful links instead:</p>
                    </div>
                    
                    <div class="buttons is-centered">
                        <?php if (isLoggedIn()): ?>
                            <a href="dashboard.php" class="button is-primary">
                                <i class="mdi mdi-view-dashboard"></i>&nbsp; Dashboard
                            </a>
                            <a href="booking.php" class="button is-info">
                                <i class="mdi mdi-calendar-clock"></i>&nbsp; Teacher Booking
                            </a>
                            <a href="vehicles.php" class="button is-success">
                                <i class="mdi mdi-map-marker-radius"></i>&nbsp; Vehicle Tracking
                            </a>
                            <a href="feedback.php" class="button is-warning">
                                <i class="mdi mdi-comment-text"></i>&nbsp; Feedback
                            </a>
                        <?php else: ?>
                            <a href="index.php" class="button is-primary">
                                <i class="mdi mdi-home"></i>&nbsp; Home
                            </a>
                            <a href="login.php" class="button is-info">
                                <i class="mdi mdi-login"></i>&nbsp; Login
                            </a>
                            <a href="register.php" class="button is-success">
                                <i class="mdi mdi-account-plus"></i>&nbsp; Register
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="buttons is-centered">
                        <button class="button is-light" onclick="history.back()">
                            <i class="mdi mdi-arrow-left"></i>&nbsp; Go Back
                        </button>
                        <a href="mailto:support@kiit.ac.in" class="button is-light">
                            <i class="mdi mdi-email"></i>&nbsp; Report Issue
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.hero.is-danger {
    background: linear-gradient(45deg, #ff3860, #ff6b9d);
}
</style>

<?php require_once '../includes/footer.php'; ?>