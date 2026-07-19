<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/log_activity.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            logActivity($conn, $user['id'], 'login', 'Logged in to the dashboard');
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}

// Already logged in? go straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$pageTitle = 'Login';
$bodyClass = 'login-body d-flex align-items-center justify-content-center';
include 'includes/head.php';
?>
    <?php include 'includes/flash.php'; ?>
    <main class="login-card shadow-lg">
        <div class="text-center mb-4">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            <h1 class="fw-bold mt-2 h3">Portfolio Dashboard</h1>
            <p class="text-muted">Sign in to manage your portfolio</p>
        </div>
        <form method="POST" action="login.php" data-no-loader>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center text-muted small mt-3">Default: admin / admin123</p>
    </main>
<?php include 'includes/footer.php'; ?>
