<?php
// includes/header.php - expects $pageTitle to be set by the including page
$pageTitle = $pageTitle ?? 'Dashboard';

$unreadCount = 0;
$notifications = [];
if (isset($conn) && isset($_SESSION['user_id'])) {
    $notifUserId = $_SESSION['user_id'];
    $countRow = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id = $notifUserId AND is_read = 0")->fetch_assoc();
    $unreadCount = $countRow['c'] ?? 0;
    $notifResult = $conn->query("SELECT * FROM notifications WHERE user_id = $notifUserId ORDER BY created_at DESC LIMIT 8");
    while ($n = $notifResult->fetch_assoc()) {
        $notifications[] = $n;
    }
}
?>
<div class="topbar">
    <h4 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
    <div class="topbar-right d-flex align-items-center">
        <div class="dropdown me-3">
            <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $unreadCount; ?>
                    </span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width:320px;max-height:400px;overflow-y:auto;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong>Notifications</strong>
                    <?php if ($unreadCount > 0): ?>
                        <a href="notifications_read.php" class="small">Mark all as read</a>
                    <?php endif; ?>
                </div>
                <?php if (empty($notifications)): ?>
                    <p class="text-muted text-center py-3 mb-0 small">No notifications yet.</p>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="px-3 py-2 border-bottom small <?php echo $n['is_read'] ? '' : 'bg-light'; ?>">
                            <div><?php echo htmlspecialchars($n['message']); ?></div>
                            <div class="text-muted" style="font-size:11px;"><?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <span class="me-3 text-muted">
            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
    </div>
</div>
