<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  // If not logged in, redirect to login page
    exit;
}

// Redirect based on user role
if ($_SESSION['role'] === 'autista') {
    header('Location: autista_dashboard.php');
} elseif ($_SESSION['role'] === 'utente') {
    header('Location: user_dashboard.php');
}
exit;
?>
