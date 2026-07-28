<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$userId = $_SESSION['user_id'];

// Stats
$totalSkills      = $conn->query("SELECT COUNT(*) AS c FROM skills WHERE user_id = $userId")->fetch_assoc()['c'];
$totalProjects    = $conn->query("SELECT COUNT(*) AS c FROM projects WHERE user_id = $userId")->fetch_assoc()['c'];
$totalCategories  = $conn->query("SELECT COUNT(*) AS c FROM categories WHERE user_id = $userId")->fetch_assoc()['c'];
$completedProjects= $conn->query("SELECT COUNT(*) AS c FROM projects WHERE user_id = $userId AND status = 'Completed'")->fetch_assoc()['c'];

// Profile completeness
$profileRow = $conn->query("SELECT * FROM profile WHERE user_id = $userId")->fetch_assoc();
$completenessFields = [
    'full_name'    => 'Full Name',
    'job_title'    => 'Job Title',
    'email'        => 'Email',
    'phone'        => 'Phone',
    'location'     => 'Location',
    'about_text'   => 'About Me',
    'profile_image'=> 'Profile Photo',
    'resume_file'  => 'Resume',
    'linkedin_url' => 'LinkedIn',
    'github_url'   => 'GitHub',
];
$completenessTotal = count($completenessFields);
$completenessDone  = 0;
$completenessItems = [];
foreach ($completenessFields as $field => $label) {
    $filled = !empty($profileRow[$field]);
    if ($filled) $completenessDone++;
    $completenessItems[] = ['label' => $label, 'done' => $filled];
}
$completenessPercent = (int)round($completenessDone / $completenessTotal * 100);

// Recent projects
$recentProjects = $conn->query("SELECT p.*, c.category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.user_id = $userId ORDER BY p.created_at DESC LIMIT 5");

// Skill distribution by category
$skillCats = $conn->query("SELECT category, COUNT(*) AS c FROM skills WHERE user_id = $userId GROUP BY category");

// Chart: Projects by Status
$statusRows = $conn->query("SELECT status, COUNT(*) AS c FROM projects WHERE user_id = $userId GROUP BY status");
$chartStatusLabels = [];
$chartStatusData   = [];
while ($r = $statusRows->fetch_assoc()) {
    $chartStatusLabels[] = $r['status'];
    $chartStatusData[]   = (int)$r['c'];
}

// Chart: Skills bar (name + proficiency)
$skillRows = $conn->query("SELECT skill_name, proficiency FROM skills WHERE user_id = $userId ORDER BY proficiency DESC LIMIT 8");
$chartSkillLabels = [];
$chartSkillData   = [];
while ($r = $skillRows->fetch_assoc()) {
    $chartSkillLabels[] = $r['skill_name'];
    $chartSkillData[]   = (int)$r['proficiency'];
}

// Recent activity log
$recentActivity = $conn->query("SELECT * FROM activity_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 8");

// User statistics
$userRow        = $conn->query("SELECT created_at FROM users WHERE id = $userId")->fetch_assoc();
$accountCreated = $userRow['created_at'] ?? null;
$totalLogins    = $conn->query("SELECT COUNT(*) AS c FROM activity_log WHERE user_id = $userId AND activity_type = 'login'")->fetch_assoc()['c'];
$lastLoginRow   = $conn->query("SELECT created_at FROM activity_log WHERE user_id = $userId AND activity_type = 'login' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$lastLogin      = $lastLoginRow['created_at'] ?? null;
$totalActivities= $conn->query("SELECT COUNT(*) AS c FROM activity_log WHERE user_id = $userId")->fetch_assoc()['c'];

function activityIcon($type) {
    $map = [
        'login'            => 'box-arrow-in-right',
        'project_added'    => 'plus-circle',
        'project_updated'  => 'pencil-square',
        'project_deleted'  => 'trash',
        'profile_updated'  => 'person-check',
        'skill_added'      => 'plus-circle',
        'skill_updated'    => 'pencil-square',
        'skill_deleted'    => 'trash',
        'category_added'   => 'tag',
        'category_updated' => 'tag',
        'category_deleted' => 'tag',
        'password_changed' => 'shield-lock',
    ];
    return $map[$type] ?? 'clock-history';
}

$activePage = 'dashboard';
$pageTitle  = 'Dashboard Overview';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/flash.php'; ?>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card fade-in-up">
                    <div class="stat-icon stat-icon-blue"><i class="bi bi-tools" aria-hidden="true"></i></div>
                    <div><h3><?php echo $totalSkills; ?></h3><p>Total Skills</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card fade-in-up">
                    <div class="stat-icon stat-icon-pink"><i class="bi bi-kanban" aria-hidden="true"></i></div>
                    <div><h3><?php echo $totalProjects; ?></h3><p>Total Projects</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card fade-in-up">
                    <div class="stat-icon stat-icon-green"><i class="bi bi-tags" aria-hidden="true"></i></div>
                    <div><h3><?php echo $totalCategories; ?></h3><p>Categories</p></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card fade-in-up">
                    <div class="stat-icon stat-icon-orange"><i class="bi bi-check2-circle" aria-hidden="true"></i></div>
                    <div><h3><?php echo $completedProjects; ?></h3><p>Completed Projects</p></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <!-- Projects by Status — Doughnut -->
            <div class="col-lg-4 col-md-6">
                <div class="panel fade-in-up h-100">
                    <h5 class="mb-3">Projects by Status</h5>
                    <?php if (empty($chartStatusData)): ?>
                        <?php
                            $emptyIcon = 'pie-chart';
                            $emptyTitle = 'No projects yet';
                            $emptyText = 'Add projects to see status breakdown.';
                            $emptyActionUrl = 'projects.php';
                            $emptyActionLabel = 'Add a project';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <div style="position:relative;height:220px;">
                            <canvas id="statusChart" aria-label="Projects by status chart" role="img"></canvas>
                        </div>
                        <div id="statusLegend" class="mt-3 d-flex flex-wrap gap-2 justify-content-center small"></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skills Proficiency — Horizontal Bar -->
            <div class="col-lg-8 col-md-6">
                <div class="panel fade-in-up h-100">
                    <h5 class="mb-3">Skills Proficiency</h5>
                    <?php if (empty($chartSkillData)): ?>
                        <?php
                            $emptyIcon = 'bar-chart';
                            $emptyTitle = 'No skills yet';
                            $emptyText = 'Add skills to see proficiency chart.';
                            $emptyActionUrl = 'skills.php';
                            $emptyActionLabel = 'Add a skill';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <div style="position:relative;height:220px;">
                            <canvas id="skillsChart" aria-label="Skills proficiency chart" role="img"></canvas>
                        </div>
                    <?php endif; ?>
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

                <!-- Recent Activities -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Recent Activities</h5>
                    <?php if ($recentActivity->num_rows === 0): ?>
                        <?php
                            $emptyIcon = 'clock-history';
                            $emptyTitle = 'No activity recorded yet';
                            $emptyText = 'Logins, edits, and updates will be tracked here.';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php while ($a = $recentActivity->fetch_assoc()): ?>
                                <li class="d-flex align-items-start gap-3 mb-3">
                                    <div class="stat-icon" style="width:38px;height:38px;font-size:16px;background:#eef0ff;color:var(--primary);flex-shrink:0;" aria-hidden="true">
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

            <!-- Right column: Skills by Category + User Statistics -->
            <div class="col-lg-4">
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Skills by Category</h5>
                    <?php
                    // Re-run query since pointer was consumed
                    $skillCats2 = $conn->query("SELECT category, COUNT(*) AS c FROM skills WHERE user_id = $userId GROUP BY category");
                    if ($skillCats2->num_rows === 0): ?>
                        <?php
                            $emptyIcon = 'tools';
                            $emptyTitle = 'No skills yet';
                            $emptyText = 'Add skills to see the category breakdown here.';
                            $emptyActionUrl = 'skills.php';
                            $emptyActionLabel = 'Add a skill';
                            include 'includes/empty_state.php';
                        ?>
                    <?php else: ?>
                        <?php while ($sc = $skillCats2->fetch_assoc()): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($sc['category']); ?></span>
                                <strong><?php echo $sc['c']; ?></strong>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <!-- Profile Completeness -->
                <div class="panel fade-in-up">
                    <h5 class="mb-1">Profile Completeness</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted"><?php echo $completenessDone; ?>/<?php echo $completenessTotal; ?> fields filled</small>
                        <strong style="color:<?php echo $completenessPercent >= 80 ? '#06d6a0' : ($completenessPercent >= 50 ? '#f9a826' : '#f72585'); ?>">
                            <?php echo $completenessPercent; ?>%
                        </strong>
                    </div>
                    <div class="skill-bar-bg mb-3" role="progressbar"
                         aria-valuenow="<?php echo $completenessPercent; ?>"
                         aria-valuemin="0" aria-valuemax="100"
                         aria-label="Profile completeness <?php echo $completenessPercent; ?>%">
                        <div class="skill-bar-fill" style="width:<?php echo $completenessPercent; ?>%;background:<?php echo $completenessPercent >= 80 ? '#06d6a0' : ($completenessPercent >= 50 ? '#f9a826' : '#f72585'); ?>;transition:width 0.6s ease;"></div>
                    </div>
                    <div class="row g-1">
                        <?php foreach ($completenessItems as $ci): ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="bi bi-<?php echo $ci['done'] ? 'check-circle-fill text-success' : 'circle text-muted'; ?>"
                                       aria-hidden="true"></i>
                                    <span class="<?php echo $ci['done'] ? '' : 'text-muted'; ?>">
                                        <?php echo htmlspecialchars($ci['label']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($completenessPercent < 100): ?>
                        <a href="profile.php" class="btn btn-sm btn-outline-primary w-100 mt-3">
                            <i class="bi bi-pencil" aria-hidden="true"></i> Complete Your Profile
                        </a>
                    <?php endif; ?>
                </div>

                <!-- User Statistics -->
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
                        <span class="text-muted">Total Activities</span>
                        <strong><?php echo $totalActivities; ?></strong>
                    </div>
                    <hr>
                    <a href="preview.php" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye" aria-hidden="true"></i> View Live Portfolio Preview
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var textColor  = isDark ? '#e0e0f0' : '#444444';
    var gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    var panelColor = isDark ? '#1a1a2e' : '#ffffff';

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

    // ---- Doughnut: Projects by Status ----
    var statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        var statusLabels = <?php echo json_encode($chartStatusLabels); ?>;
        var statusData   = <?php echo json_encode($chartStatusData); ?>;
        var statusColors = ['#06d6a0', '#4361ee', '#f9a826', '#f72585', '#9b5de5'];

        var statusChart = new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors.slice(0, statusData.length),
                    borderColor: panelColor,
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a, b){ return a + b; }, 0);
                                var pct = Math.round(ctx.parsed / total * 100);
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Custom legend
        var legend = document.getElementById('statusLegend');
        if (legend) {
            statusLabels.forEach(function(label, i) {
                var dot = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + statusColors[i] + ';margin-right:5px;"></span>';
                var item = document.createElement('span');
                item.innerHTML = dot + label + ' (' + statusData[i] + ')';
                item.style.cssText = 'display:flex;align-items:center;gap:2px;';
                legend.appendChild(item);
            });
        }
    }

    // ---- Horizontal Bar: Skills Proficiency ----
    var skillCanvas = document.getElementById('skillsChart');
    if (skillCanvas) {
        var skillLabels = <?php echo json_encode($chartSkillLabels); ?>;
        var skillData   = <?php echo json_encode($chartSkillData); ?>;

        new Chart(skillCanvas, {
            type: 'bar',
            data: {
                labels: skillLabels,
                datasets: [{
                    label: 'Proficiency (%)',
                    data: skillData,
                    backgroundColor: 'rgba(67,97,238,0.75)',
                    borderColor: '#4361ee',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        min: 0,
                        max: 100,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            callback: function(v){ return v + '%'; }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx){ return ' ' + ctx.parsed.x + '%'; }
                        }
                    }
                }
            }
        });
    }

    // Re-render charts when theme changes
    document.getElementById('themeToggle') && document.getElementById('themeToggle').addEventListener('click', function () {
        setTimeout(function () {
            var dark = document.documentElement.getAttribute('data-theme') === 'dark';
            var newText  = dark ? '#e0e0f0' : '#444444';
            var newGrid  = dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
            Chart.defaults.color = newText;
            Chart.instances && Object.values(Chart.instances).forEach(function(c) {
                if (c.options.scales) {
                    if (c.options.scales.x) {
                        c.options.scales.x.ticks.color = newText;
                        c.options.scales.x.grid.color  = newGrid;
                    }
                    if (c.options.scales.y) {
                        c.options.scales.y.ticks.color = newText;
                    }
                }
                c.update();
            });
        }, 350);
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
