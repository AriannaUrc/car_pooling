<?php
// Start the session
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'autista') {
    header('Location: login.php');
    exit;
}

echo "<h1>Welcome, {$_SESSION['username']}! You are logged in as an Autista.</h1>";
?>
