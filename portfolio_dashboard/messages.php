<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$userId = $_SESSION['user_id'];

// Mark a message as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $mid = (int)$_GET['read'];
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $mid, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location: messages.php');
    exit;
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $conn->query("UPDATE messages SET is_read = 1 WHERE user_id = $userId");
    header('Location: messages.php');
    exit;
}

// Delete a message
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $mid, $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['flash_success'] = 'Message deleted.';
    header('Location: messages.php');
    exit;
}

// Pagination
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE user_id = $userId")->fetch_assoc()['c'];
$totalPages = max(1, (int)ceil($total / $perPage));
$unread  = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE user_id = $userId AND is_read = 0")->fetch_assoc()['c'];

$messages = $conn->query("SELECT * FROM messages WHERE user_id = $userId ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

$activePage = 'messages';
$pageTitle  = 'Messages';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/flash.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0">Messages
                    <?php if ($unread > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $unread; ?> unread</span>
                    <?php endif; ?>
                </h5>
                <small class="text-muted">Messages sent from your public portfolio contact form.</small>
            </div>
            <?php if ($unread > 0): ?>
                <a href="messages.php?read_all=1" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-check2-all" aria-hidden="true"></i> Mark all as read
                </a>
            <?php endif; ?>
        </div>

        <div class="panel">
            <?php if ($messages->num_rows === 0): ?>
                <?php
                    $emptyIcon = 'envelope';
                    $emptyTitle = 'No messages yet';
                    $emptyText = 'Messages sent through your public portfolio contact form will appear here.';
                    include 'includes/empty_state.php';
                ?>
            <?php else: ?>
                <div class="list-group list-group-flush">
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="list-group-item list-group-item-action px-0 py-3 border-bottom msg-item <?php echo $msg['is_read'] ? '' : 'unread-msg'; ?>">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <!-- Avatar -->
                            <div class="msg-avatar" aria-hidden="true">
                                <?php echo strtoupper(substr($msg['sender_name'], 0, 1)); ?>
                            </div>
                            <!-- Content -->
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="msg-name">
                                        <?php if (!$msg['is_read']): ?>
                                            <span class="unread-dot" aria-label="Unread"></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($msg['sender_name']); ?>
                                    </strong>
                                    <small class="text-muted ms-2 text-nowrap"><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></small>
                                </div>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-envelope" aria-hidden="true"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($msg['sender_email']); ?>">
                                        <?php echo htmlspecialchars($msg['sender_email']); ?>
                                    </a>
                                </div>
                                <p class="mb-0 msg-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            </div>
                            <!-- Actions -->
                            <div class="d-flex flex-column gap-2 ms-2">
                                <?php if (!$msg['is_read']): ?>
                                    <a href="messages.php?read=<?php echo $msg['id']; ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Mark message from <?php echo htmlspecialchars($msg['sender_name']); ?> as read"
                                       title="Mark as read">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="messages.php?delete=<?php echo $msg['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   aria-label="Delete message from <?php echo htmlspecialchars($msg['sender_name']); ?>"
                                   title="Delete"
                                   onclick="return confirm('Delete this message?')">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4" aria-label="Messages pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.msg-avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4361ee, #7209b7);
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.unread-msg { background: var(--primary-light) !important; }
.unread-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--primary);
    margin-right: 6px;
    vertical-align: middle;
}
.msg-name { font-size: 15px; color: var(--text); }
.msg-text { color: var(--text); font-size: 14px; line-height: 1.6; }
.list-group-item { background: transparent !important; border-color: var(--border) !important; }
.list-group-item-action { color: var(--text); }
.list-group-item-action:hover { background: var(--table-stripe) !important; }
[data-theme="dark"] .list-group-item { background: transparent !important; border-color: rgba(255,255,255,0.07) !important; }
[data-theme="dark"] .unread-msg { background: rgba(67,97,238,0.06) !important; }
[data-theme="dark"] .msg-text { color: #e2e2f5 !important; }
[data-theme="dark"] .msg-name { color: #e2e2f5 !important; }
[data-theme="dark"] .text-muted { color: #8080aa !important; }
[data-theme="dark"] .panel { background: #13132a !important; border-color: rgba(255,255,255,0.07) !important; }
[data-theme="dark"] body { background: #0b0b18 !important; color: #e2e2f5 !important; }
[data-theme="dark"] .wrapper { background: #0b0b18 !important; }
[data-theme="dark"] .main-content { background: #0b0b18 !important; }
</style>

<?php include 'includes/footer.php'; ?>
