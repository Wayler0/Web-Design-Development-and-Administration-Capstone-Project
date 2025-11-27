<?php
require_once 'layouts/header.php';
requireRole('student');

if (!isset($_GET['id'])) {
    header('Location: dashboard_student.php');
    exit();
}

$enrollment_id = (int)$_GET['id'];
$student_id = $_SESSION['user_id'];

// Verify ownership and get enrollment/class details
$query = "SELECT c.class_code, c.class_name, c.description, u.full_name as lecturer_name, e.grade
          FROM classes c
          JOIN users u ON c.lecturer_id = u.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.id = $enrollment_id AND e.student_id = $student_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error_message'] = "Enrollment not found or you don't have permission to view it.";
    header('Location: dashboard_student.php');
    exit();
}

$class = mysqli_fetch_assoc($result);

// Fetch attendance history
$attendance_query = "SELECT * FROM attendance WHERE enrollment_id = $enrollment_id ORDER BY attendance_date DESC";
$attendance_result = mysqli_query($conn, $attendance_query);

// Calculate attendance stats
$present = 0;
$absent = 0;
$late = 0;
$total = 0;
if ($attendance_result) {
    $total = mysqli_num_rows($attendance_result);
    while ($row = mysqli_fetch_assoc($attendance_result)) {
        if ($row['status'] === 'present') $present++;
        if ($row['status'] === 'absent') $absent++;
        if ($row['status'] === 'late') $late++;
    }
    mysqli_data_seek($attendance_result, 0); // Reset pointer
}
$attendance_rate = $total > 0 ? (($present + $late * 0.5) / $total) * 100 : 0; // Example: late counts as half present

?>

<div class="container">
    <div class="page-header">
        <h2>Class Details: <?php echo htmlspecialchars($class['class_code']); ?> - <?php echo htmlspecialchars($class['class_name']); ?></h2>
        <a href="dashboard_student.php" class="btn">Back to Dashboard</a>
    </div>

    <div class="class-details-container">
        <div class="class-info">
            <h3>Class Information</h3>
            <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($class['description']); ?></p>
        </div>

        <div class="student-grade">
            <h3>Your Grade</h3>
            <p class="grade-display"><?php echo htmlspecialchars($class['grade'] ?? 'Not Graded Yet'); ?></p>
        </div>

        <div class="attendance-history">
            <h3>Your Attendance</h3>
            <div class="attendance-stats">
                <p><strong>Attendance Rate:</strong> <?php echo round($attendance_rate, 2); ?>%</p>
                <p><strong>Present:</strong> <?php echo $present; ?></p>
                <p><strong>Absent:</strong> <?php echo $absent; ?></p>
                <p><strong>Late:</strong> <?php echo $late; ?></p>
            </div>

            <?php if ($total > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($attendance_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
                                <td class="status-<?php echo htmlspecialchars($row['status']); ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No attendance records found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
