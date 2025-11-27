<?php
require_once 'config/config.php';

// Check if user is logged in and redirect
if (isLoggedIn()) {
    if (hasRole('lecturer')) {
        header('Location: views/dashboard_lecturer.php');
        exit();
    } elseif (hasRole('student')) {
        header('Location: views/dashboard_student.php');
        exit();
    }
}

// If not logged in or role is not set, redirect to login
header('Location: login.php');
exit();
?>