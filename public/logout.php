<?php
require_once '../includes/auth.php';

// Perform logout
$auth->logout();

// Redirect to home page with success message
header('Location: index.php?success=You have been successfully logged out.');
exit();
?>