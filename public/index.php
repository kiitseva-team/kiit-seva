<?php
$pageTitle = 'Home';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!-- Hero Section -->
<section class="hero is-primary is-medium">
    <div class="hero-body">
        <div class="container has-text-centered">
            <h1 class="title is-1">
                <i class="mdi mdi-school"></i> KIIT SEVA
            </h1>
            <h2 class="subtitle is-4">
                Your Gateway to Smart Campus Life
            </h2>
            <p class="is-size-5 mb-5">
                Streamline your campus experience with teacher bookings, real-time vehicle tracking, and instant feedback submission.
            </p>
            <div class="buttons is-centered">
                <a href="login.php" class="button is-white is-large">
                    <i class="mdi mdi-login"></i>&nbsp; Get Started
                </a>
                <a href="register.php" class="button is-primary is-outlined is-large">
                    <i class="mdi mdi-account-plus"></i>&nbsp; Register Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container">
        <div class="has-text-centered mb-6">
            <h2 class="title is-2">Smart Campus Solutions</h2>
            <p class="subtitle is-5">Everything you need for efficient campus management</p>
        </div>
        
        <div class="columns is-multiline">
            <div class="column is-one-third">
                <div class="card">
                    <div class="card-content has-text-centered">
                        <div class="content">
                            <i class="mdi mdi-calendar-clock is-size-1 has-text-primary"></i>
                            <h4 class="title is-4">Teacher Booking</h4>
                            <p>
                                Easily schedule meetings with faculty members. Browse available time slots and book appointments for academic guidance, project discussions, or consultations.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="column is-one-third">
                <div class="card">
                    <div class="card-content has-text-centered">
                        <div class="content">
                            <i class="mdi mdi-map-marker-radius is-size-1 has-text-info"></i>
                            <h4 class="title is-4">Vehicle Tracking</h4>
                            <p>
                                Track campus buses and staff vehicles in real-time. View current locations, routes, and estimated arrival times to plan your commute efficiently.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="column is-one-third">
                <div class="card">
                    <div class="card-content has-text-centered">
                        <div class="content">
                            <i class="mdi mdi-comment-text-multiple is-size-1 has-text-success"></i>
                            <h4 class="title is-4">Feedback System</h4>
                            <p>
                                Share your thoughts and suggestions about campus services. Submit feedback anonymously or with your identity to help improve university operations.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="section has-background-light">
    <div class="container">
        <div class="columns has-text-centered">
            <div class="column">
                <div class="box">
                    <i class="mdi mdi-account-group is-size-1 has-text-primary"></i>
                    <h3 class="title is-3">500+</h3>
                    <p class="subtitle">Active Users</p>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <i class="mdi mdi-calendar-check is-size-1 has-text-info"></i>
                    <h3 class="title is-3">1000+</h3>
                    <p class="subtitle">Bookings Made</p>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <i class="mdi mdi-bus is-size-1 has-text-success"></i>
                    <h3 class="title is-3">15+</h3>
                    <p class="subtitle">Vehicles Tracked</p>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <i class="mdi mdi-star is-size-1 has-text-warning"></i>
                    <h3 class="title is-3">4.8/5</h3>
                    <p class="subtitle">User Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it Works Section -->
<section class="section">
    <div class="container">
        <div class="has-text-centered mb-6">
            <h2 class="title is-2">How It Works</h2>
            <p class="subtitle is-5">Get started in just a few simple steps</p>
        </div>
        
        <div class="columns is-vcentered">
            <div class="column is-half">
                <div class="content is-large">
                    <div class="media">
                        <div class="media-left">
                            <span class="icon is-large has-background-primary has-text-white" style="border-radius: 50%; padding: 1rem;">
                                <i class="mdi mdi-account-plus is-size-3"></i>
                            </span>
                        </div>
                        <div class="media-content">
                            <h4 class="title is-4">1. Register Account</h4>
                            <p>Create your account with your KIIT email ID and choose your role (student, teacher, or staff).</p>
                        </div>
                    </div>
                    
                    <div class="media mt-5">
                        <div class="media-left">
                            <span class="icon is-large has-background-info has-text-white" style="border-radius: 50%; padding: 1rem;">
                                <i class="mdi mdi-view-dashboard is-size-3"></i>
                            </span>
                        </div>
                        <div class="media-content">
                            <h4 class="title is-4">2. Access Dashboard</h4>
                            <p>Log in to your personalized dashboard with features tailored to your role and requirements.</p>
                        </div>
                    </div>
                    
                    <div class="media mt-5">
                        <div class="media-left">
                            <span class="icon is-large has-background-success has-text-white" style="border-radius: 50%; padding: 1rem;">
                                <i class="mdi mdi-rocket-launch is-size-3"></i>
                            </span>
                        </div>
                        <div class="media-content">
                            <h4 class="title is-4">3. Start Using Services</h4>
                            <p>Book teacher appointments, track vehicles, submit feedback, and manage your campus activities efficiently.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="column is-half has-text-centered">
                <figure class="image">
                    <i class="mdi mdi-laptop is-size-1" style="font-size: 15rem; color: #3273dc;"></i>
                </figure>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section has-background-primary">
    <div class="container has-text-centered">
        <h2 class="title is-2 has-text-white">Ready to Get Started?</h2>
        <p class="subtitle is-4 has-text-white">Join hundreds of KIIT community members already using SEVA</p>
        <div class="buttons is-centered mt-5">
            <a href="register.php" class="button is-white is-large">
                <i class="mdi mdi-account-plus"></i>&nbsp; Register Now
            </a>
            <a href="login.php" class="button is-primary is-outlined is-large has-text-white">
                <i class="mdi mdi-login"></i>&nbsp; Login
            </a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>