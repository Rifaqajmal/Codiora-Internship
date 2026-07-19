<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/log_activity.php';
require_once 'includes/file_helper.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handles project image upload. Returns: string filename, null (no file given), or false (invalid file).
function handleProjectImage($userId) {
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['project_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) return false;
        if ($_FILES['project_image']['size'] > 3 * 1024 * 1024) return false;
        $newName = 'project_' . $userId . '_' . time() . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['project_image']['tmp_name'], 'assets/uploads/' . $newName)) {
            return $newName;
        }
        return false;
    }
    return null;
}

// ---- POST processing must happen BEFORE any header/include output ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add new category
    if (isset($_POST['add_category'])) {
        $catName = trim($_POST['category_name']);
        if ($catName !== '') {
            $stmt = $conn->prepare("INSERT INTO categories (user_id, category_name) VALUES (?, ?)");
            $stmt->bind_param("is", $userId, $catName);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $userId, 'category_added', "Added category: $catName");
            $success = "Category added successfully.";
        } else {
            $error = "Category name cannot be empty.";
        }
    }

    // Edit category
    if (isset($_POST['edit_category'])) {
        $catId = (int) $_POST['category_id'];
        $catName = trim($_POST['category_name']);
        if ($catName === '') {
            $error = "Category name cannot be empty.";
        } else {
            $stmt = $conn->prepare("UPDATE categories SET category_name=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sii", $catName, $catId, $userId);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $userId, 'category_updated', "Renamed category to: $catName");
            $success = "Category updated successfully.";
        }
    }

    // Delete category
    if (isset($_POST['delete_category'])) {
        $catId = (int) $_POST['category_id'];

        $nameStmt = $conn->prepare("SELECT category_name FROM categories WHERE id=? AND user_id=?");
        $nameStmt->bind_param("ii", $catId, $userId);
        $nameStmt->execute();
        $deletedCatName = $nameStmt->get_result()->fetch_assoc()['category_name'] ?? 'Unknown';
        $nameStmt->close();

        $stmt = $conn->prepare("DELETE FROM categories WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $catId, $userId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $userId, 'category_deleted', "Deleted category: $deletedCatName");
        $success = "Category deleted. Its projects are now Uncategorized.";
    }

    // Add new project
    if (isset($_POST['add_project'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $categoryId = (int) $_POST['category_id'];
        $technology = trim($_POST['technology']);
        $projectLink = trim($_POST['project_link']);
        $githubLink = trim($_POST['github_link']);
        $status = $_POST['status'];

        if ($title === '') {
            $error = "Project title is required.";
        } else {
            $img = handleProjectImage($userId);
            if ($img === false) {
                $error = "Invalid image file. Use JPG/PNG/WEBP under 3MB.";
            } else {
                $imageName = $img ?? 'default_project.png';
                // 9 params: i(user_id) s(title) s(desc) i(category_id) s(link) s(github) s(image) s(tech) s(status) = ississssss... verified below
                $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, category_id, project_link, github_link, project_image, technology, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ississsss", $userId, $title, $description, $categoryId, $projectLink, $githubLink, $imageName, $technology, $status);
                $stmt->execute();
                $stmt->close();
                logActivity($conn, $userId, 'project_added', "Added project: $title");
                $success = "Project added successfully.";
            }
        }
    }

    // Edit existing project
    if (isset($_POST['edit_project'])) {
        $id = (int) $_POST['project_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $categoryId = (int) $_POST['category_id'];
        $technology = trim($_POST['technology']);
        $projectLink = trim($_POST['project_link']);
        $githubLink = trim($_POST['github_link']);
        $status = $_POST['status'];

        $img = handleProjectImage($userId);
        if ($img === false) {
            $error = "Invalid image file. Use JPG/PNG/WEBP under 3MB.";
        } else {
            if ($img !== null) {
                // A new image was uploaded — fetch the old filename first so we can
                // remove it from disk after the DB row is updated (Image & File Management).
                $oldStmt = $conn->prepare("SELECT project_image FROM projects WHERE id=? AND user_id=?");
                $oldStmt->bind_param("ii", $id, $userId);
                $oldStmt->execute();
                $oldImage = $oldStmt->get_result()->fetch_assoc()['project_image'] ?? null;
                $oldStmt->close();

                // 10 params: s s i s s s s s i i
                $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, category_id=?, project_link=?, github_link=?, technology=?, status=?, project_image=? WHERE id=? AND user_id=?");
                $stmt->bind_param("ssisssssii", $title, $description, $categoryId, $projectLink, $githubLink, $technology, $status, $img, $id, $userId);
                $stmt->execute();
                $stmt->close();

                deleteUploadedFile($oldImage, 'default_project.png');
            } else {
                // 9 params: s s i s s s s i i
                $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, category_id=?, project_link=?, github_link=?, technology=?, status=? WHERE id=? AND user_id=?");
                $stmt->bind_param("ssissssii", $title, $description, $categoryId, $projectLink, $githubLink, $technology, $status, $id, $userId);
                $stmt->execute();
                $stmt->close();
            }
            $success = "Project updated successfully.";
            logActivity($conn, $userId, 'project_updated', "Updated project: $title");
        }
    }

    // Delete project
    if (isset($_POST['delete_project'])) {
        $id = (int) $_POST['project_id'];

        $nameStmt = $conn->prepare("SELECT title, project_image FROM projects WHERE id=? AND user_id=?");
        $nameStmt->bind_param("ii", $id, $userId);
        $nameStmt->execute();
        $deletedRow = $nameStmt->get_result()->fetch_assoc();
        $deletedTitle = $deletedRow['title'] ?? 'Unknown';
        $deletedImage = $deletedRow['project_image'] ?? null;
        $nameStmt->close();

        $stmt = $conn->prepare("DELETE FROM projects WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $stmt->close();

        deleteUploadedFile($deletedImage, 'default_project.png');

        logActivity($conn, $userId, 'project_deleted', "Deleted project: $deletedTitle");
        $success = "Project deleted successfully.";
    }
}

// ---- Search, Filter & Pagination (GET) ----
$searchTerm = trim($_GET['search'] ?? '');
$filterCategory = (int) ($_GET['category'] ?? 0);
$filterStatus = trim($_GET['status'] ?? '');
$filterTech = trim($_GET['technology'] ?? '');

$perPage = 6;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$whereClauses = ["p.user_id = ?"];
$params = [$userId];
$types = 'i';

if ($searchTerm !== '') {
    $whereClauses[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $like = "%$searchTerm%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if ($filterCategory > 0) {
    $whereClauses[] = "p.category_id = ?";
    $params[] = $filterCategory;
    $types .= 'i';
}
if ($filterStatus !== '') {
    $whereClauses[] = "p.status = ?";
    $params[] = $filterStatus;
    $types .= 's';
}
if ($filterTech !== '') {
    $whereClauses[] = "p.technology LIKE ?";
    $params[] = "%$filterTech%";
    $types .= 's';
}

$whereSql = implode(' AND ', $whereClauses);

// Total count for pagination
$countQuery = "SELECT COUNT(*) AS total FROM projects p WHERE $whereSql";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalProjects = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = max(1, ceil($totalProjects / $perPage));

// Main paginated query
$query = "SELECT p.*, c.category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereSql ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$paramsWithLimit = $params;
$paramsWithLimit[] = $perPage;
$paramsWithLimit[] = $offset;
$typesWithLimit = $types . 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
$stmt->execute();
$projects = $stmt->get_result();

// Categories list for dropdowns
$categoryList = [];
$catResult = $conn->query("SELECT * FROM categories WHERE user_id = $userId ORDER BY category_name");
while ($row = $catResult->fetch_assoc()) {
    $categoryList[] = $row;
}

// Build query string helper for pagination links (preserves filters)
function buildPageUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'projects.php?' . http_build_query($params);
}

$activePage = 'projects';
$pageTitle = 'Project Management';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/flash.php'; ?>

        <!-- Toolbar: Search, Filter, Add buttons -->
        <div class="panel fade-in-up">
            <form method="GET" action="projects.php" class="row g-2 align-items-end mb-3">
                <div class="col-md-3">
                    <label class="form-label small">Search Projects</label>
                    <input type="text" name="search" class="form-control" placeholder="Title or description"
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($categoryList as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filterCategory == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['Completed', 'In Progress', 'Planned'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $filterStatus === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Technology</label>
                    <input type="text" name="technology" class="form-control" placeholder="e.g. React, PHP"
                           value="<?php echo htmlspecialchars($filterTech); ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                    <a href="projects.php" class="btn btn-outline-secondary flex-fill"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Projects (<?php echo $totalProjects; ?>)</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                        <i class="bi bi-tags"></i> Manage Categories
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                        <i class="bi bi-plus-lg"></i> Add Project
                    </button>
                </div>
            </div>
        </div>

        <!-- Project Cards -->
        <div class="row g-3">
            <?php if ($projects->num_rows === 0): ?>
                <div class="col-12">
                    <div class="panel">
                        <?php
                            $emptyIcon = ($searchTerm !== '' || $filterCategory > 0 || $filterStatus !== '' || $filterTech !== '') ? 'search' : 'kanban';
                            $emptyTitle = ($searchTerm !== '' || $filterCategory > 0 || $filterStatus !== '' || $filterTech !== '') ? 'No projects match your filters' : 'No projects yet';
                            $emptyText = ($searchTerm !== '' || $filterCategory > 0 || $filterStatus !== '' || $filterTech !== '') ? 'Try clearing the search or filters above.' : 'Add your first project to see it appear here and on your live portfolio.';
                            $emptyActionUrl = 'modal:addProjectModal';
                            $emptyActionLabel = 'Add Project';
                            include 'includes/empty_state.php';
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <?php while ($p = $projects->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="panel project-card p-0 h-100 d-flex flex-column">
                            <?php
                                $imgPath = 'assets/uploads/' . $p['project_image'];
                                $imgExists = $p['project_image'] !== 'default_project.png' && file_exists($imgPath);
                                $imgSrc = $imgExists ? htmlspecialchars($imgPath) : 'https://placehold.co/400x200?text=' . urlencode($p['title']);
                            ?>
                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($p['title']); ?>"
                                 style="cursor:pointer;"
                                 onclick='openProjectDetails(<?php echo json_encode($p); ?>, "<?php echo $imgSrc; ?>")'>
                            <div class="p-3 flex-fill d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0" style="cursor:pointer;" onclick='openProjectDetails(<?php echo json_encode($p); ?>, "<?php echo $imgSrc; ?>")'>
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </h6>
                                    <span class="badge bg-<?php echo $p['status'] === 'Completed' ? 'success' : ($p['status'] === 'In Progress' ? 'warning' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars($p['status']); ?>
                                    </span>
                                </div>
                                <span class="category-badge mb-2" style="width:fit-content;"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                                <p class="text-muted small flex-fill"><?php echo htmlspecialchars(mb_strimwidth($p['description'], 0, 90, '...')); ?></p>
                                <div class="d-flex gap-2 mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick='openProjectDetails(<?php echo json_encode($p); ?>, "<?php echo $imgSrc; ?>")'>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary flex-fill"
                                        data-bs-toggle="modal" data-bs-target="#editProjectModal"
                                        data-id="<?php echo $p['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($p['title']); ?>"
                                        data-description="<?php echo htmlspecialchars($p['description']); ?>"
                                        data-category="<?php echo $p['category_id']; ?>"
                                        data-technology="<?php echo htmlspecialchars($p['technology']); ?>"
                                        data-link="<?php echo htmlspecialchars($p['project_link']); ?>"
                                        data-github="<?php echo htmlspecialchars($p['github_link']); ?>"
                                        data-status="<?php echo htmlspecialchars($p['status']); ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" action="projects.php" onsubmit="return confirm('Delete this project?');">
                                        <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="delete_project" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo buildPageUrl(max(1, $page - 1)); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPageUrl($i); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo buildPageUrl(min($totalPages, $page + 1)); ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Manage Categories Modal (Add/Edit/Delete) -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group mb-3">
                    <?php foreach ($categoryList as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <form method="POST" action="projects.php" class="d-flex gap-2 flex-fill align-items-center">
                                <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                                <input type="text" name="category_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($c['category_name']); ?>">
                                <button type="submit" name="edit_category" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i></button>
                            </form>
                            <form method="POST" action="projects.php" class="ms-2" onsubmit="return confirm('Delete this category? Projects in it will become Uncategorized.');">
                                <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($categoryList)): ?>
                        <li class="list-group-item text-muted">No categories yet.</li>
                    <?php endif; ?>
                </ul>
                <form method="POST" action="projects.php" class="d-flex gap-2">
                    <input type="text" name="category_name" class="form-control" placeholder="New category name" required>
                    <button type="submit" name="add_category" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="projects.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="0">Uncategorized</option>
                                <?php foreach ($categoryList as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Technologies Used</label>
                            <input type="text" name="technology" class="form-control" placeholder="e.g. PHP, MySQL, Bootstrap 5 (comma separated)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Live Link</label>
                            <input type="url" name="project_link" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GitHub Link</label>
                            <input type="url" name="github_link" class="form-control" placeholder="https://github.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Completed">Completed</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Planned">Planned</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Project Image</label>
                            <input type="file" name="project_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="projects.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="project_id" id="edit_project_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="edit_category_id" class="form-select">
                                <option value="0">Uncategorized</option>
                                <?php foreach ($categoryList as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Technologies Used</label>
                            <input type="text" name="technology" id="edit_technology" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Live Link</label>
                            <input type="url" name="project_link" id="edit_link" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GitHub Link</label>
                            <input type="url" name="github_link" id="edit_github" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Completed">Completed</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Planned">Planned</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Replace Image (optional)</label>
                            <input type="file" name="project_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_project" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project Details Modal (read-only, opened by clicking a project card) -->
<div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-labelledby="projectDetailsLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectDetailsLabel">Project Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="pd_image" src="" alt="" class="w-100 mb-3" style="border-radius:10px; max-height:300px; object-fit:cover;">
                <h4 id="pd_title"></h4>
                <span id="pd_status" class="badge mb-2"></span>
                <span id="pd_category" class="category-badge mb-2 ms-2"></span>
                <p id="pd_description" class="mt-3"></p>
                <div id="pd_tech_wrap" class="mb-3">
                    <strong>Technologies:</strong>
                    <div id="pd_tech" class="mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    <a id="pd_link" href="#" target="_blank" class="btn btn-primary btn-sm">Live Demo</a>
                    <a id="pd_github" href="#" target="_blank" class="btn btn-outline-dark btn-sm">GitHub</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editProjectModal').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('edit_project_id').value = btn.getAttribute('data-id');
    document.getElementById('edit_title').value = btn.getAttribute('data-title');
    document.getElementById('edit_description').value = btn.getAttribute('data-description');
    document.getElementById('edit_category_id').value = btn.getAttribute('data-category');
    document.getElementById('edit_technology').value = btn.getAttribute('data-technology');
    document.getElementById('edit_link').value = btn.getAttribute('data-link');
    document.getElementById('edit_github').value = btn.getAttribute('data-github');
    document.getElementById('edit_status').value = btn.getAttribute('data-status');
});

// Project Details Modal logic (accessible: keyboard-focusable trigger via card click)
function openProjectDetails(project, imgSrc) {
    document.getElementById('pd_image').src = imgSrc;
    document.getElementById('pd_title').textContent = project.title;
    document.getElementById('pd_description').textContent = project.description || 'No description provided.';

    const statusEl = document.getElementById('pd_status');
    statusEl.textContent = project.status;
    statusEl.className = 'badge mb-2 bg-' + (project.status === 'Completed' ? 'success' : project.status === 'In Progress' ? 'warning' : 'secondary');

    document.getElementById('pd_category').textContent = project.category_name || 'Uncategorized';

    const techWrap = document.getElementById('pd_tech_wrap');
    const techEl = document.getElementById('pd_tech');
    if (project.technology && project.technology.trim() !== '') {
        techEl.innerHTML = '';
        project.technology.split(',').forEach(t => {
            const span = document.createElement('span');
            span.className = 'badge bg-light text-dark border me-1';
            span.textContent = t.trim();
            techEl.appendChild(span);
        });
        techWrap.style.display = 'block';
    } else {
        techWrap.style.display = 'none';
    }

    const linkBtn = document.getElementById('pd_link');
    const githubBtn = document.getElementById('pd_github');
    linkBtn.style.display = project.project_link ? 'inline-block' : 'none';
    linkBtn.href = project.project_link || '#';
    githubBtn.style.display = project.github_link ? 'inline-block' : 'none';
    githubBtn.href = project.github_link || '#';

    new bootstrap.Modal(document.getElementById('projectDetailsModal')).show();
}
</script>
<?php include 'includes/footer.php'; ?>
