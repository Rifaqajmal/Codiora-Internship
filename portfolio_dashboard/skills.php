<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/log_activity.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// ---- POST processing before any output ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_skill'])) {
        $name = trim($_POST['skill_name']);
        $proficiency = (int) $_POST['proficiency'];
        $category = trim($_POST['category']);

        if ($name === '') {
            $error = "Skill name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO skills (user_id, skill_name, proficiency, category) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isis", $userId, $name, $proficiency, $category);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $userId, 'skill_added', "Added skill: $name");
            $success = "Skill added successfully.";
        }
    }

    if (isset($_POST['edit_skill'])) {
        $id = (int) $_POST['skill_id'];
        $name = trim($_POST['skill_name']);
        $proficiency = (int) $_POST['proficiency'];
        $category = trim($_POST['category']);

        $stmt = $conn->prepare("UPDATE skills SET skill_name=?, proficiency=?, category=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sisii", $name, $proficiency, $category, $id, $userId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $userId, 'skill_updated', "Updated skill: $name");
        $success = "Skill updated successfully.";
    }

    if (isset($_POST['delete_skill'])) {
        $id = (int) $_POST['skill_id'];

        $nameStmt = $conn->prepare("SELECT skill_name FROM skills WHERE id=? AND user_id=?");
        $nameStmt->bind_param("ii", $id, $userId);
        $nameStmt->execute();
        $deletedName = $nameStmt->get_result()->fetch_assoc()['skill_name'] ?? 'Unknown';
        $nameStmt->close();

        $stmt = $conn->prepare("DELETE FROM skills WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $userId, 'skill_deleted', "Deleted skill: $deletedName");
        $success = "Skill deleted successfully.";
    }
}

$skills = $conn->query("SELECT * FROM skills WHERE user_id = $userId ORDER BY category, skill_name");

$activePage = 'skills';
$pageTitle = 'Skills Management';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/flash.php'; ?>

        <div class="panel fade-in-up">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">All Skills</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                    <i class="bi bi-plus-lg"></i> Add Skill
                </button>
            </div>

            <?php if ($skills->num_rows === 0): ?>
                <?php
                    $emptyIcon = 'tools';
                    $emptyTitle = 'No skills added yet';
                    $emptyText = 'Add a skill to show your proficiency here and on your live portfolio.';
                    $emptyActionUrl = 'modal:addSkillModal';
                    $emptyActionLabel = 'Add Skill';
                    include 'includes/empty_state.php';
                ?>
            <?php else: ?>
                <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Skill</th><th>Category</th><th>Proficiency</th><th width="120">Actions</th></tr></thead>
                    <tbody>
                    <?php while ($s = $skills->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['skill_name']); ?></td>
                            <td><span class="category-badge"><?php echo htmlspecialchars($s['category']); ?></span></td>
                            <td style="width:200px;">
                                <div class="skill-bar-bg">
                                    <div class="skill-bar-fill" style="width: <?php echo $s['proficiency']; ?>%;"></div>
                                </div>
                                <small class="text-muted"><?php echo $s['proficiency']; ?>%</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#editSkillModal"
                                    data-id="<?php echo $s['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($s['skill_name']); ?>"
                                    data-proficiency="<?php echo $s['proficiency']; ?>"
                                    data-category="<?php echo htmlspecialchars($s['category']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="skills.php" class="d-inline" onsubmit="return confirm('Delete this skill?');">
                                    <input type="hidden" name="skill_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" name="delete_skill" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="skills.php">
                <div class="modal-header">
                    <h5 class="modal-title">Add Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Skill Name</label>
                        <input type="text" name="skill_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Frontend, Backend, Tools" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proficiency (%)</label>
                        <input type="number" name="proficiency" class="form-control" min="0" max="100" value="50" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Skill Modal -->
<div class="modal fade" id="editSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="skills.php">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="skill_id" id="edit_skill_id">
                    <div class="mb-3">
                        <label class="form-label">Skill Name</label>
                        <input type="text" name="skill_name" id="edit_skill_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" id="edit_category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proficiency (%)</label>
                        <input type="number" name="proficiency" id="edit_proficiency" class="form-control" min="0" max="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_skill" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editSkillModal').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('edit_skill_id').value = btn.getAttribute('data-id');
    document.getElementById('edit_skill_name').value = btn.getAttribute('data-name');
    document.getElementById('edit_category').value = btn.getAttribute('data-category');
    document.getElementById('edit_proficiency').value = btn.getAttribute('data-proficiency');
});
</script>
<?php include 'includes/footer.php'; ?>
