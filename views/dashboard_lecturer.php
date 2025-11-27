<?php
require_once 'layouts/header.php';
requireRole('lecturer');

$lecturer_id = $_SESSION['user_id'];

// Handle class creation
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $class_code = sanitize($_POST['class_code']);
    $class_name = sanitize($_POST['class_name']);
    $description = sanitize($_POST['description']);
    $max_students = (int)$_POST['max_students'];
    $schedule_day = sanitize($_POST['schedule_day']);
    $schedule_time = sanitize($_POST['schedule_time']);
    $room = sanitize($_POST['room']);

    if (empty($class_code) || empty($class_name) || empty($max_students)) {
        $error = "Class Code, Class Name, and Max Students are required.";
    } else {
        $query = "INSERT INTO classes (class_code, class_name, description, lecturer_id, max_students, schedule_day, schedule_time, room) 
                  VALUES ('$class_code', '$class_name', '$description', $lecturer_id, $max_students, '$schedule_day', '$schedule_time', '$room')";
        if (mysqli_query($conn, $query)) {
            $success = "Class created successfully.";
        } else {
            $error = "Error creating class: " . mysqli_error($conn);
        }
    }
}

// Display session messages
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Fetch lecturer's classes
$query = "SELECT c.*, COUNT(e.id) as enrolled_count
          FROM classes c
          LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
          WHERE c.lecturer_id = $lecturer_id
          GROUP BY c.id
          ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>Lecturer Dashboard</h2>
        <button id="showCreateClassForm" class="btn">Create New Class</button>
    </div>

    <p>Welcome to your dashboard. Here you can manage your classes, view enrolled students, and manage grades and attendance.</p>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Create Class Form (hidden by default) -->
    <div id="createClassForm" class="form-container" style="display: none;">
        <h3>Create a New Class</h3>
        <form method="POST" action="">
            <input type="hidden" name="create_class" value="1">
            <div class="form-group">
                <label for="class_code">Class Code</label>
                <input type="text" id="class_code" name="class_code" required>
            </div>
            <div class="form-group">
                <label for="class_name">Class Name</label>
                <input type="text" id="class_name" name="class_name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="max_students">Max Students</label>
                <input type="number" id="max_students" name="max_students" value="30" required>
            </div>
            <div class="form-group">
                <label for="schedule_day">Schedule Day</label>
                <input type="text" id="schedule_day" name="schedule_day">
            </div>
            <div class="form-group">
                <label for="schedule_time">Schedule Time</label>
                <input type="time" id="schedule_time" name="schedule_time">
            </div>
            <div class="form-group">
                <label for="room">Room</label>
                <input type="text" id="room" name="room">
            </div>
            <button type="submit" class="btn">Create Class</button>
        </form>
    </div>

    <h3>Your Classes</h3>
    <div class="grid class-grid">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($result)): ?>
                <div class="card class-card">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($class['class_code']); ?> - <?php echo htmlspecialchars($class['class_name']); ?></h4>
                        <p class="card-text"><?php echo htmlspecialchars($class['description']); ?></p>
                        <p><strong>Schedule:</strong> <?php echo htmlspecialchars($class['schedule_day']); ?> at <?php echo htmlspecialchars(date('g:i A', strtotime($class['schedule_time']))); ?></p>
                        <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                        <p><strong>Enrolled:</strong> <?php echo $class['enrolled_count']; ?> / <?php echo $class['max_students']; ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="class_details.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">View Details</a>
                        <a href="delete_class.php?id=<?php echo $class['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this class?')">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have not created any classes yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('showCreateClassForm').addEventListener('click', function() {
        var form = document.getElementById('createClassForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
</script>

<?php require_once 'layouts/footer.php'; ?>