<?php
$pageTitle = 'Login';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($auth->login($username, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-half">
                <div class="box">
                    <div class="has-text-centered mb-5">
                        <h1 class="title is-3">
                            <i class="mdi mdi-login has-text-primary"></i> Login to KIIT SEVA
                        </h1>
                        <p class="subtitle is-6">Welcome back! Please sign in to your account.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="notification is-danger is-light">
                            <button class="delete"></button>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="field">
                            <label class="label">Username or Email</label>
                            <div class="control has-icons-left">
                                <input class="input" type="text" name="username" placeholder="Enter username or email" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-account"></i>
                                </span>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Password</label>
                            <div class="control has-icons-left">
                                <input class="input" type="password" name="password" placeholder="Enter password" required>
                                <span class="icon is-small is-left">
                                    <i class="mdi mdi-lock"></i>
                                </span>
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <button class="button is-primary is-fullwidth" type="submit">
                                    <i class="mdi mdi-login"></i>&nbsp; Login
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <div class="has-text-centered">
                        <p>
                            Don't have an account? 
                            <a href="register.php" class="has-text-primary">
                                <strong>Register here</strong>
                            </a>
                        </p>
                    </div>

                    <!-- Demo Credentials -->
                    <div class="notification is-info is-light mt-4">
                        <p class="has-text-weight-semibold mb-2">Demo Credentials:</p>
                        <div class="content">
                            <strong>Admin:</strong> admin / password<br>
                            <strong>Teacher:</strong> dr.sharma / password<br>
                            <strong>Student:</strong> student1 / password
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill demo credentials
    const demoButtons = document.querySelectorAll('.demo-fill');
    demoButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const role = this.dataset.role;
            const usernameField = document.querySelector('input[name="username"]');
            const passwordField = document.querySelector('input[name="password"]');
            
            switch(role) {
                case 'admin':
                    usernameField.value = 'admin';
                    break;
                case 'teacher':
                    usernameField.value = 'dr.sharma';
                    break;
                case 'student':
                    usernameField.value = 'student1';
                    break;
            }
            passwordField.value = 'password';
        });
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const username = document.querySelector('input[name="username"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        
        if (!username || !password) {
            e.preventDefault();
            showNotification('Please fill in all fields.', 'danger');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>