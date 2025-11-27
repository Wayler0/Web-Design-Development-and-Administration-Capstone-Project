<?php
require_once 'layouts/header.php';
requireRole('lecturer');

if (!isset($_GET['id'])) {
    header('Location: dashboard_lecturer.php');
    exit();
}

$class_id = (int)$_GET['id'];
$lecturer_id = $_SESSION['user_id'];

// Verify ownership and get class details
$query = "SELECT * FROM classes WHERE id = $class_id AND lecturer_id = $lecturer_id";
$class_result = mysqli_query($conn, $query);

if (mysqli_num_rows($class_result) === 0) {
    $_SESSION['error_message'] = "Class not found or you don't have permission to view it.";
    header('Location: dashboard_lecturer.php');
    exit();
}

$class = mysqli_fetch_assoc($class_result);

$error = '';
$success = '';

// Handle grade update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $grade = sanitize($_POST['grade']);

    $verify_query = "SELECT * FROM enrollments WHERE id = $enrollment_id AND class_id = $class_id";
    $verify_result = mysqli_query($conn, $verify_query);
    if (mysqli_num_rows($verify_result) > 0) {
        $update_query = "UPDATE enrollments SET grade = '$grade' WHERE id = $enrollment_id";
        if (mysqli_query($conn, $update_query)) {
            $success = "Grade updated successfully.";
        } else {
            $error = "Error updating grade: " . mysqli_error($conn);
        }
    } else {
        $error = "Invalid enrollment record.";
    }
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $attendance_date = sanitize($_POST['attendance_date']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    $verify_query = "SELECT * FROM enrollments WHERE id = $enrollment_id AND class_id = $class_id";
    $verify_result = mysqli_query($conn, $verify_query);
    if (mysqli_num_rows($verify_result) > 0) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to avoid errors on re-marking
        $attendance_query = "INSERT INTO attendance (enrollment_id, attendance_date, status, notes)
                             VALUES ($enrollment_id, '$attendance_date', '$status', '$notes')
                             ON DUPLICATE KEY UPDATE status = '$status', notes = '$notes'";
        if (mysqli_query($conn, $attendance_query)) {
            $success = "Attendance marked successfully.";
        } else {
            $error = "Error marking attendance: " . mysqli_error($conn);
        }
    } else {
        $error = "Invalid enrollment record.";
    }
}

// Fetch enrolled students with attendance rate
$students_query = "SELECT u.full_name, u.email, e.id as enrollment_id, e.grade,
                          (SELECT COUNT(*) FROM attendance WHERE enrollment_id = e.id AND status = 'present') as present_count,
                          (SELECT COUNT(*) FROM attendance WHERE enrollment_id = e.id) as total_sessions
                   FROM users u
                   JOIN enrollments e ON u.id = e.student_id
                   WHERE e.class_id = $class_id AND e.status = 'enrolled'";
$students_result = mysqli_query($conn, $students_query);
?>

<div class="container">
    <div class="page-header">
        <h2>Class Details: <?php echo htmlspecialchars($class['class_code']); ?> - <?php echo htmlspecialchars($class['class_name']); ?></h2>
        <a href="dashboard_lecturer.php" class="btn">Back to Dashboard</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="class-details-container">
        <div class="class-info">
            <h3>Class Information</h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($class['description']); ?></p>
            <p><strong>Schedule:</strong> <?php echo htmlspecialchars($class['schedule_day']); ?> at <?php echo htmlspecialchars(date('g:i A', strtotime($class['schedule_time']))); ?></p>
            <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
            <p><strong>Max Students:</strong> <?php echo $class['max_students']; ?></p>
        </div>

        <div class="enrolled-students">
            <h3>Enrolled Students</h3>
            <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Grade</th>
                            <th>Attendance Rate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <form method="POST" action="" class="form-inline">
                                        <input type="hidden" name="enrollment_id" value="<?php echo $student['enrollment_id']; ?>">
                                        <input type="text" name="grade" class="form-control" value="<?php echo htmlspecialchars($student['grade']); ?>">
                                        <button type="submit" name="update_grade" class="btn btn-sm">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <?php
                                    $rate = $student['total_sessions'] > 0 ? ($student['present_count'] / $student['total_sessions']) * 100 : 0;
                                    echo round($rate) . '%';
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="showAttendanceModal(<?php echo $student['enrollment_id']; ?>)">Mark Attendance</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No students are currently enrolled in this class.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-button" onclick="closeAttendanceModal()">&times;</span>
        <h3>Mark Attendance</h3>
        <form method="POST" action="">
            <input type="hidden" name="mark_attendance" value="1">
            <input type="hidden" id="modalEnrollmentId" name="enrollment_id" value="">
            <div class="form-group">
                <label for="attendance_date">Date</label>
                <input type="date" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"></textarea>
            </div>
            <button type="submit" class="btn">Save Attendance</button>
        </form>
    </div>
</div>

<script>
    function showAttendanceModal(enrollmentId) {
        document.getElementById('modalEnrollmentId').value = enrollmentId;
        document.getElementById('attendanceModal').style.display = 'block';
    }

    function closeAttendanceModal() {
        document.getElementById('attendanceModal').style.display = 'none';
    }
</script>

<?php require_once 'layouts/footer.php'; ?>