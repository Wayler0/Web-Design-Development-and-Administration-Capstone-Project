<?php
require_once '../config/config.php';
requireRole('lecturer');

if (!isset($_GET['id'])) {
    header('Location: dashboard_lecturer.php');
    exit();
}

$class_id = (int)$_GET['id'];
$lecturer_id = $_SESSION['user_id'];

// Verify ownership
$query = "SELECT * FROM classes WHERE id = $class_id AND lecturer_id = $lecturer_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    // Not the owner, or class doesn't exist
    header('Location: dashboard_lecturer.php');
    exit();
}

// Delete the class
$delete_query = "DELETE FROM classes WHERE id = $class_id";
if (mysqli_query($conn, $delete_query)) {
    // Optionally, set a success message in the session
    $_SESSION['success_message'] = "Class deleted successfully.";
} else {
    // Optionally, set an error message
    $_SESSION['error_message'] = "Error deleting class: " . mysqli_error($conn);
}

header('Location: dashboard_lecturer.php');
exit();
?>
