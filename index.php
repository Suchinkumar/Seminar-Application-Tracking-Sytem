<?php
session_start();

// Suppress Notices
error_reporting(E_ALL & ~E_NOTICE);

// Your existing code
if (isset($_SESSION['username'])) {
    // User is already logged in, redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // User is not logged in, display login form
    include 'login.php';
}
?>
