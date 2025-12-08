<?php
/* ==========================================================
   Dynamic Class Management Application - CONFIG FILE
   ========================================================== */

// -----------------------------------------
// Start Session
// -----------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// -----------------------------------------
// Database Configuration
// -----------------------------------------
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dcma';


// -----------------------------------------
// Create Database Connection
// -----------------------------------------
$conn = new mysqli($host, $user, $pass, $db);


// -----------------------------------------
// Check Connection and Handle Errors
// -----------------------------------------
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// -----------------------------------------
// Set Charset to UTF-8
// -----------------------------------------
$conn->set_charset("utf8");


// ==========================================================
// HELPER FUNCTIONS
// ==========================================================

// -----------------------------------------
// Check if user is logged in
// -----------------------------------------
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


// -----------------------------------------
// Check if user has a specific role
// -----------------------------------------
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}


// -----------------------------------------
// Require login to access a page
// -----------------------------------------
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}


// -----------------------------------------
// Require specific role to access a page
// -----------------------------------------
function requireRole($role) {
    requireLogin(); // First check user is logged in

    if (!hasRole($role)) {
        header("Location: unauthorized.php");
        exit();
    }
}


// -----------------------------------------
// Sanitize user input to prevent SQL injection
// -----------------------------------------
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

?>
 
