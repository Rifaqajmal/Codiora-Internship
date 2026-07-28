<?php
$activePage = $activePage ?? '';

$unreadMessages = 0;
if (isset($conn) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $unreadMessages = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE user_id = $uid AND is_read = 0")->fetch_assoc()['c'] ?? 0;
}
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
        </div>
        <span>PortfolioAdmin</span>
    </div>

    <nav aria-label="Main navigation">
        <div class="sidebar-section-label">Main Menu</div>
        <ul class="sidebar-menu">
            <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                <a href="dashboard.php" <?php echo $activePage === 'dashboard' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-speedometer2" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>">
                <a href="profile.php" <?php echo $activePage === 'profile' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="<?php echo $activePage === 'skills' ? 'active' : ''; ?>">
                <a href="skills.php" <?php echo $activePage === 'skills' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-tools" aria-hidden="true"></i>
                    <span>Skills</span>
                </a>
            </li>
            <li class="<?php echo $activePage === 'projects' ? 'active' : ''; ?>">
                <a href="projects.php" <?php echo $activePage === 'projects' ? 'aria-current="page"' : ''; ?>>
                    <i class="bi bi-kanban" aria-hidden="true"></i>
                    <span>Projects</span>
                </a>
            </li>
            <li class="<?php echo $activePage === 'messages' ? 'active' : ''; ?>">
                <a href="messages.php"
                   <?php echo $activePage === 'messages' ? 'aria-current="page"' : ''; ?>
                   aria-label="Messages<?php echo $unreadMessages > 0 ? " ($unreadMessages unread)" : ''; ?>">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <span>Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="badge bg-danger ms-auto" aria-hidden="true"
                              style="font-size:10px;padding:3px 7px;border-radius:10px;">
                            <?php echo $unreadMessages; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Portfolio</div>
        <ul class="sidebar-menu" style="padding-top:0;">
            <li>
                <a href="preview.php" target="_blank" rel="noopener"
                   aria-label="Live Preview (opens in new tab)">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    <span>Live Preview</span>
                    <i class="bi bi-box-arrow-up-right ms-auto" style="font-size:11px;opacity:0.5;" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
