<?php
require_once 'config/config.php';

// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit();
?>