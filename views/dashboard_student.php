<?php
require_once 'layouts/header.php';
requireRole('student');

$student_id = $_SESSION['user_id'];

// Handle enrollment and drop actions
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll'])) {
        $class_id = (int)$_POST['class_id'];
        // Check if already enrolled or class is full
        $check_query = "SELECT * FROM enrollments WHERE student_id = $student_id AND class_id = $class_id";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $error = "You are already enrolled in this class.";
        } else {
            $class_query = "SELECT max_students, (SELECT COUNT(*) FROM enrollments WHERE class_id = $class_id AND status = 'enrolled') as enrolled_count FROM classes WHERE id = $class_id";
            $class_result = mysqli_query($conn, $class_query);
            $class_data = mysqli_fetch_assoc($class_result);
            if ($class_data['enrolled_count'] >= $class_data['max_students']) {
                $error = "This class is full.";
            } else {
                $enroll_query = "INSERT INTO enrollments (student_id, class_id) VALUES ($student_id, $class_id)";
                if (mysqli_query($conn, $enroll_query)) {
                    $success = "You have successfully enrolled in the class.";
                } else {
                    $error = "Error enrolling in class: " . mysqli_error($conn);
                }
            }
        }
    } elseif (isset($_POST['drop'])) {
        $enrollment_id = (int)$_POST['enrollment_id'];
        $drop_query = "UPDATE enrollments SET status = 'dropped' WHERE id = $enrollment_id AND student_id = $student_id";
        if (mysqli_query($conn, $drop_query)) {
            $success = "You have successfully dropped the class.";
        } else {
            $error = "Error dropping class: " . mysqli_error($conn);
        }
    }
}


// Fetch enrolled classes
$enrolled_query = "SELECT c.class_code, c.class_name, c.description, u.full_name as lecturer_name, e.id as enrollment_id, e.grade
                   FROM classes c
                   JOIN users u ON c.lecturer_id = u.id
                   JOIN enrollments e ON c.id = e.class_id
                   WHERE e.student_id = $student_id AND e.status = 'enrolled'";
$enrolled_result = mysqli_query($conn, $enrolled_query);

// Fetch available classes
$available_query = "SELECT c.*, u.full_name as lecturer_name, 
                           (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = 'enrolled') as enrolled_count
                    FROM classes c
                    JOIN users u ON c.lecturer_id = u.id
                    WHERE c.status = 'open' AND c.id NOT IN (SELECT class_id FROM enrollments WHERE student_id = $student_id AND status = 'enrolled')";
$available_result = mysqli_query($conn, $available_query);
?>

<div class="dashboard-container">
    <h2>Student Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>. Here you can manage your classes.</p>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <h3>Your Enrolled Classes</h3>
    <div class="grid class-grid">
        <?php if ($enrolled_result && mysqli_num_rows($enrolled_result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($enrolled_result)): ?>
                <div class="card class-card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($class['class_code']); ?> - <?php echo htmlspecialchars($class['class_name']); ?></h4>
                        <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                        <p><strong>Your Grade:</strong> <?php echo htmlspecialchars($class['grade'] ?? 'Not Graded'); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="class_view.php?id=<?php echo $class['enrollment_id']; ?>" class="btn btn-primary">View Details</a>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="enrollment_id" value="<?php echo $class['enrollment_id']; ?>">
                            <button type="submit" name="drop" class="btn btn-danger" onclick="return confirm('Are you sure you want to drop this class?')">Drop Class</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You are not enrolled in any classes.</p>
        <?php endif; ?>
    </div>

    <hr>

    <h3>Available Classes</h3>
    <div class="grid class-grid">
        <?php if ($available_result && mysqli_num_rows($available_result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($available_result)): ?>
                <div class="card class-card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($class['class_code']); ?> - <?php echo htmlspecialchars($class['class_name']); ?></h4>
                        <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                        <p><strong>Enrolled:</strong> <?php echo $class['enrolled_count']; ?> / <?php echo $class['max_students']; ?></p>
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="">
                            <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                            <button type="submit" name="enroll" class="btn">Enroll</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No classes are available for enrollment at this time.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
