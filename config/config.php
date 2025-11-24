<?php
/****************************************************
 * Dynamic Class Management Application (DCMA)
 * CONFIGURATION FILE
 ****************************************************/

// ------------------------------
// 1. Start session
// ------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------
// 2. Database configuration
// ------------------------------
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dcma';

// ------------------------------
// 3. Create database connection
// ------------------------------
$conn = new mysqli($host, $user, $pass, $db);

// ------------------------------
// 4. Check connection
// ------------------------------
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ------------------------------
// 5. Set charset
// ------------------------------
$conn->set_charset("utf8");

// ------------------------------
// 6. Helper functions
// ------------------------------

/**
 * Sanitize user input to prevent SQL injection & XSS
 */
function sanitize($data) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged-in user has a specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect user if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Redirect user if they don't have the required role
 */
function requireRole($role) {
    if (!hasRole($role)) {
        header("Location: unauthorized.php");
        exit();
    }
}

?>
