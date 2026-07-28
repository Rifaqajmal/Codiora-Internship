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
            $_SESSION['user_id']  = $user['id'];
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

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$pageTitle = 'Login';
$bodyClass = 'login-body';
include 'includes/head.php';
?>

<main class="login-card" role="main">
    <div class="text-center mb-4">
        <div class="login-brand-icon">
            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
        </div>
        <h1 class="fw-bold h4 mb-1">Portfolio Dashboard</h1>
        <p class="mb-0" style="color:rgba(255,255,255,0.55);font-size:13.5px;">Sign in to manage your portfolio</p>
    </div>

    <?php if ($error): ?>
        <div class="alert mb-3" role="alert"
             style="background:rgba(247,37,133,0.15);color:#f72585;border:1px solid rgba(247,37,133,0.25);border-radius:9px;font-size:13.5px;padding:11px 14px;">
            <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" data-no-loader>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="input-group-custom">
                <input type="text" id="username" name="username"
                       class="form-control" required autofocus
                       autocomplete="username"
                       placeholder="Enter your username">
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control" required
                   autocomplete="current-password"
                   placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Sign In
        </button>
    </form>

    <p class="text-center mt-4 mb-0" style="font-size:12px;color:rgba(255,255,255,0.3);">
        Portfolio Management System &copy; <?php echo date('Y'); ?>
    </p>
</main>

<?php include 'includes/footer.php'; ?>
