<?php
session_start();
require_once dirname(__DIR__) . '/config/db.php';

class Auth {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    // Login user
    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE (username = :username OR email = :username) AND status = "active"');
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if ($user && password_verify($password, $user->password)) {
            $this->setUserSession($user);
            return true;
        }
        return false;
    }

    // Register new user
    public function register($data) {
        // Check if username or email already exists
        $this->db->query('SELECT id FROM users WHERE username = :username OR email = :email');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        
        if ($this->db->single()) {
            return false; // User already exists
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $this->db->query('INSERT INTO users (username, email, password, role, full_name, phone, department) 
                         VALUES (:username, :email, :password, :role, :full_name, :phone, :department)');
        
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':department', $data['department']);

        if ($this->db->execute()) {
            // If teacher role, add to teachers table
            if ($data['role'] == 'teacher') {
                $userId = $this->db->lastInsertId();
                $this->db->query('INSERT INTO teachers (user_id, department, designation, office_location) 
                                 VALUES (:user_id, :department, :designation, :office_location)');
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':department', $data['department']);
                $this->db->bind(':designation', $data['designation'] ?? 'Assistant Professor');
                $this->db->bind(':office_location', $data['office_location'] ?? 'TBA');
                $this->db->execute();
            }
            return true;
        }
        return false;
    }

    // Set user session
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $user->role;
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['department'] = $user->department;
        $_SESSION['logged_in'] = true;
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Get current user
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $_SESSION['user_id']);
        return $this->db->single();
    }

    // Check user role
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    // Check if user has any of the given roles
    public function hasAnyRole($roles) {
        if (!isset($_SESSION['role'])) {
            return false;
        }
        return in_array($_SESSION['role'], $roles);
    }

    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
    }

    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /kiit-seva/public/login.php');
            exit();
        }
    }

    // Require specific role
    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: /kiit-seva/public/dashboard.php?error=unauthorized');
            exit();
        }
    }

    // Require any of the given roles
    public function requireAnyRole($roles) {
        $this->requireLogin();
        if (!$this->hasAnyRole($roles)) {
            header('Location: /kiit-seva/public/dashboard.php?error=unauthorized');
            exit();
        }
    }

    // Get user's full name
    public function getUserName() {
        return $_SESSION['full_name'] ?? 'User';
    }

    // Get user role
    public function getUserRole() {
        return $_SESSION['role'] ?? 'guest';
    }

    // Get user ID
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Get user department
    public function getUserDepartment() {
        return $_SESSION['department'] ?? null;
    }
}

// Initialize auth instance
$auth = new Auth();

// Helper functions
function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

function requireLogin() {
    global $auth;
    $auth->requireLogin();
}

function requireRole($role) {
    global $auth;
    $auth->requireRole($role);
}

function requireAnyRole($roles) {
    global $auth;
    $auth->requireAnyRole($roles);
}

function getUserName() {
    global $auth;
    return $auth->getUserName();
}

function getUserRole() {
    global $auth;
    return $auth->getUserRole();
}

function getUserId() {
    global $auth;
    return $auth->getUserId();
}
?>