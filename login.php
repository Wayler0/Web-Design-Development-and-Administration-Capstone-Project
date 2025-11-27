<?php
require_once 'config/config.php';

// If already logged in, redirect to the appropriate dashboard
if (isLoggedIn()) {
    if (hasRole('lecturer')) {
        header('Location: views/dashboard_lecturer.php');
    } else {
        header('Location: views/dashboard_student.php');
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password before verification

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect to appropriate dashboard
                if (hasRole('lecturer')) {
                    header('Location: views/dashboard_lecturer.php');
                } else {
                    header('Location: views/dashboard_student.php');
                }
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DCMA</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Dynamic Class Management</h1>
            <h2>Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <p><strong>Lecturer:</strong> mwangi_lecturer / password123</p>
                <p><strong>Student:</strong> akinyi_student / password123</p>
            </div>
        </div>
    </div>
</body>
</html>