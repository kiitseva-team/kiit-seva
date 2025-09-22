<?php
$pageTitle = 'Register';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'role' => $_POST['role'],
        'full_name' => trim($_POST['full_name']),
        'phone' => trim($_POST['phone']),
        'department' => trim($_POST['department'])
    ];
    
    // Additional fields for teachers
    if ($data['role'] === 'teacher') {
        $data['designation'] = trim($_POST['designation'] ?? '');
        $data['office_location'] = trim($_POST['office_location'] ?? '');
    }
    
    // Validation
    if (empty($data['username']) || empty($data['email']) || empty($data['password']) || 
        empty($data['full_name']) || empty($data['department'])) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($data['username']) < 3) {
        $error = 'Username must be at least 3 characters long.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!str_ends_with(strtolower($data['email']), '@kiit.ac.in')) {
        $error = 'Please use your KIIT email address (@kiit.ac.in).';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        if ($auth->register($data)) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Username or email already exists. Please try different credentials.';
        }
    }
}

$departments = [
    'Computer Science', 'Electronics', 'Mechanical', 'Civil', 'Electrical', 'IT',
    'Biotechnology', 'Chemical', 'Aerospace', 'Management', 'Law', 'Other'
];
?>

<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-two-thirds">
                <div class="box">
                    <div class="has-text-centered mb-5">
                        <h1 class="title is-3">
                            <i class="mdi mdi-account-plus has-text-primary"></i> Register for KIIT SEVA
                        </h1>
                        <p class="subtitle is-6">Join the smart campus community</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="notification is-danger is-light">
                            <button class="delete"></button>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="notification is-success is-light">
                            <button class="delete"></button>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="registerForm">
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Username <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="username" placeholder="Choose a username" 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-account"></i>
                                        </span>
                                    </div>
                                    <p class="help">At least 3 characters, no spaces</p>
                                </div>
                            </div>

                            <div class="column">
                                <div class="field">
                                    <label class="label">Full Name <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="full_name" placeholder="Your full name" 
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-account-card-details"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Email <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="email" name="email" placeholder="your.email@kiit.ac.in" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-email"></i>
                                        </span>
                                    </div>
                                    <p class="help">Must use KIIT email address</p>
                                </div>
                            </div>

                            <div class="column">
                                <div class="field">
                                    <label class="label">Phone Number</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="tel" name="phone" placeholder="Your phone number" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-phone"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Role <span class="has-text-danger">*</span></label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="role" id="roleSelect" required>
                                                <option value="">Select your role</option>
                                                <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                                                <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                                                <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="column">
                                <div class="field">
                                    <label class="label">Department <span class="has-text-danger">*</span></label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="department" required>
                                                <option value="">Select department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo $dept; ?>" 
                                                            <?php echo (isset($_POST['department']) && $_POST['department'] === $dept) ? 'selected' : ''; ?>>
                                                        <?php echo $dept; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Teacher-specific fields -->
                        <div id="teacherFields" style="display: none;">
                            <div class="columns">
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Designation</label>
                                        <div class="control has-icons-left">
                                            <input class="input" type="text" name="designation" placeholder="e.g., Assistant Professor" 
                                                   value="<?php echo isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : ''; ?>">
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-account-tie"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="column">
                                    <div class="field">
                                        <label class="label">Office Location</label>
                                        <div class="control has-icons-left">
                                            <input class="input" type="text" name="office_location" placeholder="e.g., CS Block, Room 301" 
                                                   value="<?php echo isset($_POST['office_location']) ? htmlspecialchars($_POST['office_location']) : ''; ?>">
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-map-marker"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Password <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="password" name="password" placeholder="Create password" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-lock"></i>
                                        </span>
                                    </div>
                                    <p class="help">At least 6 characters</p>
                                </div>
                            </div>

                            <div class="column">
                                <div class="field">
                                    <label class="label">Confirm Password <span class="has-text-danger">*</span></label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="password" name="confirm_password" placeholder="Confirm password" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-lock-check"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" required>
                                    I agree to the <a href="#" class="has-text-primary">Terms of Service</a> and 
                                    <a href="#" class="has-text-primary">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <button class="button is-primary is-fullwidth" type="submit">
                                    <i class="mdi mdi-account-plus"></i>&nbsp; Register
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <div class="has-text-centered">
                        <p>
                            Already have an account? 
                            <a href="login.php" class="has-text-primary">
                                <strong>Login here</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const teacherFields = document.getElementById('teacherFields');
    const form = document.getElementById('registerForm');

    // Show/hide teacher fields based on role selection
    roleSelect.addEventListener('change', function() {
        if (this.value === 'teacher') {
            teacherFields.style.display = 'block';
        } else {
            teacherFields.style.display = 'none';
        }
    });

    // Initialize on page load
    if (roleSelect.value === 'teacher') {
        teacherFields.style.display = 'block';
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        const email = document.querySelector('input[name="email"]').value;
        const username = document.querySelector('input[name="username"]').value;

        // Username validation
        if (username.length < 3) {
            e.preventDefault();
            showNotification('Username must be at least 3 characters long.', 'danger');
            return;
        }

        if (username.includes(' ')) {
            e.preventDefault();
            showNotification('Username cannot contain spaces.', 'danger');
            return;
        }

        // Email validation
        if (!email.toLowerCase().endsWith('@kiit.ac.in')) {
            e.preventDefault();
            showNotification('Please use your KIIT email address (@kiit.ac.in).', 'danger');
            return;
        }

        // Password validation
        if (password.length < 6) {
            e.preventDefault();
            showNotification('Password must be at least 6 characters long.', 'danger');
            return;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            showNotification('Passwords do not match.', 'danger');
            return;
        }
    });

    // Real-time password matching feedback
    const passwordField = document.querySelector('input[name="password"]');
    const confirmPasswordField = document.querySelector('input[name="confirm_password"]');

    function checkPasswordMatch() {
        if (confirmPasswordField.value && passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.classList.add('is-danger');
        } else {
            confirmPasswordField.classList.remove('is-danger');
        }
    }

    passwordField.addEventListener('input', checkPasswordMatch);
    confirmPasswordField.addEventListener('input', checkPasswordMatch);
});
</script>

<?php require_once '../includes/footer.php'; ?>