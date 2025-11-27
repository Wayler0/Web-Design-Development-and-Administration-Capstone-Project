<?php
session_start(); // Start session

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = 'Bang1ad3sh';
$db = 'dcma';

// Create database connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection and handle errors
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, 'utf8');

// Helper functions

/**
 * Checks if a user is logged in.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has a specific role.
 * @param string $role The role to check against (e.g., 'student', 'lecturer').
 * @return bool True if the user has the role, false otherwise.
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirects to the login page if the user is not logged in.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirects to the login page if the user does not have the required role.
 * @param string $role The required role.
 */
function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: login.php'); // Or an unauthorized page
        exit();
    }
}

/**
 * Sanitizes user input to prevent SQL injection and XSS.
 * @param string $data The input string to sanitize.
 * @return string The sanitized string.
 */
function sanitize($data) {
    global $conn; // Use the global connection object
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

?>