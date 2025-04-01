<?php
session_start();
include 'db_connection.php';

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit;
}