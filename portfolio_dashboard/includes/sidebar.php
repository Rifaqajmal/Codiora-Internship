<?php
// includes/sidebar.php - expects $activePage to be set by the including page
$activePage = $activePage ?? '';
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>PortfolioAdmin</span>
    </div>
    <ul class="sidebar-menu">
        <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>">
            <a href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
        </li>
        <li class="<?php echo $activePage === 'skills' ? 'active' : ''; ?>">
            <a href="skills.php"><i class="bi bi-tools"></i> Skills</a>
        </li>
        <li class="<?php echo $activePage === 'projects' ? 'active' : ''; ?>">
            <a href="projects.php"><i class="bi bi-kanban"></i> Projects</a>
        </li>
        <li>
            <a href="preview.php" target="_blank"><i class="bi bi-eye"></i> Live Preview</a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>
