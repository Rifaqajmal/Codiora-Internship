<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$userId = $_SESSION['user_id'];

// Stats
$totalSkills = $conn->query("SELECT COUNT(*) AS c FROM skills WHERE user_id = $userId")->fetch_assoc()['c'];
$totalProjects = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE user_id = $userId")->fetch_assoc()['c'];
$totalCategories = $conn->query("SELECT COUNT(*) AS c FROM categories WHERE user_id = $userId")->fetch_assoc()['c'];
$completedProjects = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE user_id = $userId AND status = 'Completed'")->fetch_assoc()['c'];

// Recent projects
$recentProjects = $conn->query("SELECT p.*, c.category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.user_id = $userId ORDER BY p.created_at DESC LIMIT 5");

// Skill distribution by category
$skillCats = $conn->query("SELECT category, COUNT(*) AS c FROM skills WHERE user_id = $userId GROUP BY category");

// Recent activity log (Week 3)
$recentActivity = $conn->query("SELECT * FROM activity_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 8");

// User statistics (Week 3): account age, total logins, last login
$userRow = $conn->query("SELECT created_at FROM users WHERE id = $userId")->fetch_assoc();
$accountCreated = $userRow['created_at'] ?? null;
$totalLogins = $conn->query("SELECT COUNT(*) AS c FROM activity_log WHERE user_id = $userId AND activity_type = 'login'")->fetch_assoc()['c'];
$lastLoginRow = $conn->query("SELECT created_at FROM activity_log WHERE user_id = $userId AND activity_type = 'login' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$lastLogin = $lastLoginRow['created_at'] ?? null;
$totalActivities = $conn->query("SELECT COUNT(*) AS c FROM activity_log WHERE user_id = $userId")->fetch_assoc()['c'];

// Map activity types to icons for the feed
function activityIcon($type) {
    $map = [
        'login' => 'box-arrow-in-right',
        'project_added' => 'plus-circle',
        'project_updated' => 'pencil-square',
        'project_deleted' => 'trash',
        'profile_updated' => 'person-check',
        'skill_added' => 'plus-circle',
        'skill_updated' => 'pencil-square',
        'skill_deleted' => 'trash',
        'category_added' => 'tag',
        'category_updated' => 'tag',
        'category_deleted' => 'tag',
        'password_changed' => 'shield-lock',
    ];
    return $map[$type] ?? 'clock-history';
}

$activePage = 'dashboard';
$pageTitle = 'Dashboard Overview';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#4361ee;"><i class="bi bi-tools"></i></div>
                    <div><h3><?php echo $totalSkills; ?></h3><p>Total Skills</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#f72585;"><i class="bi bi-kanban"></i></div>
                    <div><h3><?php echo $totalProjects; ?></h3><p>Total Projects</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#06d6a0;"><i class="bi bi-tags"></i></div>
                    <div><h3><?php echo $totalCategories; ?></h3><p>Categories</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#f9a826;"><i class="bi bi-check2-circle"></i></div>
                    <div><h3><?php echo $completedProjects; ?></h3><p>Completed Projects</p></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Recent Projects -->
            <div class="col-lg-8">
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Recent Projects</h5>
                    <?php if ($recentProjects->num_rows === 0): ?>
                        <?php
                            $emptyIcon = 'kanban';
                            $emptyTitle = 'No projects yet';
                            $emptyText = 'Your most recently added projects will show up here.';
                            $emptyActionUrl = 'projects.php';
                            $emptyActionLabel = 'Add your first project';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Added</th></tr></thead>
                            <tbody>
                            <?php while ($p = $recentProjects->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                                    <td><span class="category-badge"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['status']); ?></td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities (Week 3) -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Recent Activities</h5>
                    <?php if ($recentActivity->num_rows === 0): ?>
                        <?php
                            $emptyIcon = 'clock-history';
                            $emptyTitle = 'No activity recorded yet';
                            $emptyText = 'Logins, edits, and updates will be tracked here as you use the dashboard.';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php while ($a = $recentActivity->fetch_assoc()): ?>
                                <li class="d-flex align-items-start gap-3 mb-3">
                                    <div class="stat-icon" style="width:38px;height:38px;font-size:16px;background:#eef0ff;color:var(--primary);flex-shrink:0;">
                                        <i class="bi bi-<?php echo activityIcon($a['activity_type']); ?>"></i>
                                    </div>
                                    <div>
                                        <div><?php echo htmlspecialchars($a['description']); ?></div>
                                        <div class="text-muted small"><?php echo date('M d, Y \a\t h:i A', strtotime($a['created_at'])); ?></div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skill Categories Breakdown + User Statistics -->
            <div class="col-lg-4">
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Skills by Category</h5>
                    <?php if ($skillCats->num_rows === 0): ?>
                        <?php
                            $emptyIcon = 'tools';
                            $emptyTitle = 'No skills yet';
                            $emptyText = 'Add skills to see the category breakdown here.';
                            $emptyActionUrl = 'skills.php';
                            $emptyActionLabel = 'Add a skill';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <?php while ($sc = $skillCats->fetch_assoc()): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($sc['category']); ?></span>
                                <strong><?php echo $sc['c']; ?></strong>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <!-- User Statistics (Week 3) -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">User Statistics</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Account Created</span>
                        <strong><?php echo $accountCreated ? date('M d, Y', strtotime($accountCreated)) : 'N/A'; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Logins</span>
                        <strong><?php echo $totalLogins; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Login</span>
                        <strong><?php echo $lastLogin ? date('M d, Y h:i A', strtotime($lastLogin)) : 'N/A'; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Activities Logged</span>
                        <strong><?php echo $totalActivities; ?></strong>
                    </div>
                    <hr>
                    <a href="preview.php" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> View Live Portfolio Preview
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
