<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/log_activity.php';
require_once 'includes/file_helper.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';
$pwSuccess = '';
$pwError = '';

// ---- POST processing must happen BEFORE any header/include output ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_info'])) {
        $fullName = trim($_POST['full_name']);
        $jobTitle = trim($_POST['job_title']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $location = trim($_POST['location']);
        $linkedinUrl = trim($_POST['linkedin_url']);
        $githubUrl = trim($_POST['github_url']);
        $twitterUrl = trim($_POST['twitter_url']);

        $stmt = $conn->prepare("UPDATE profile SET full_name=?, job_title=?, email=?, phone=?, location=?, linkedin_url=?, github_url=?, twitter_url=? WHERE user_id=?");
        $stmt->bind_param("ssssssssi", $fullName, $jobTitle, $email, $phone, $location, $linkedinUrl, $githubUrl, $twitterUrl, $userId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $userId, 'profile_updated', 'Updated personal information');
        $success = "Personal information updated successfully.";
    }

    if (isset($_POST['update_about'])) {
        $about = trim($_POST['about_text']);
        $stmt = $conn->prepare("UPDATE profile SET about_text=? WHERE user_id=?");
        $stmt->bind_param("si", $about, $userId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $userId, 'profile_updated', 'Updated About section');
        $success = "About section updated successfully.";
    }

    if (isset($_POST['upload_image']) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                $error = "Image must be smaller than 2MB.";
            } else {
                $newName = 'profile_' . $userId . '_' . time() . '.' . $ext;
                $destPath = 'assets/uploads/' . $newName;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destPath)) {
                    // Grab the old filename before overwriting, so we can remove it from disk.
                    $oldImgStmt = $conn->prepare("SELECT profile_image FROM profile WHERE user_id=?");
                    $oldImgStmt->bind_param("i", $userId);
                    $oldImgStmt->execute();
                    $oldImage = $oldImgStmt->get_result()->fetch_assoc()['profile_image'] ?? null;
                    $oldImgStmt->close();

                    $stmt = $conn->prepare("UPDATE profile SET profile_image=? WHERE user_id=?");
                    $stmt->bind_param("si", $newName, $userId);
                    $stmt->execute();
                    $stmt->close();

                    deleteUploadedFile($oldImage, 'default.png');

                    logActivity($conn, $userId, 'profile_updated', 'Uploaded a new profile image');
                    $success = "Profile image updated successfully.";
                } else {
                    $error = "Failed to upload image.";
                }
            }
        } else {
            $error = "Only JPG, JPEG, PNG, WEBP files are allowed.";
        }
    }

    // Resume upload (Professional Contact Experience - Download Resume Button)
    if (isset($_POST['upload_resume']) && isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['resume_file']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $error = "Resume must be a PDF file.";
        } elseif ($_FILES['resume_file']['size'] > 5 * 1024 * 1024) {
            $error = "Resume must be smaller than 5MB.";
        } else {
            $newName = 'resume_' . $userId . '_' . time() . '.pdf';
            $destPath = 'assets/uploads/' . $newName;
            if (move_uploaded_file($_FILES['resume_file']['tmp_name'], $destPath)) {
                $oldResumeStmt = $conn->prepare("SELECT resume_file FROM profile WHERE user_id=?");
                $oldResumeStmt->bind_param("i", $userId);
                $oldResumeStmt->execute();
                $oldResume = $oldResumeStmt->get_result()->fetch_assoc()['resume_file'] ?? null;
                $oldResumeStmt->close();

                $stmt = $conn->prepare("UPDATE profile SET resume_file=? WHERE user_id=?");
                $stmt->bind_param("si", $newName, $userId);
                $stmt->execute();
                $stmt->close();

                deleteUploadedFile($oldResume, null);

                logActivity($conn, $userId, 'profile_updated', 'Uploaded a new resume');
                $success = "Resume uploaded successfully.";
            } else {
                $error = "Failed to upload resume.";
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$userRow || !password_verify($currentPassword, $userRow['password'])) {
            $pwError = "Current password is incorrect.";
        } elseif (strlen($newPassword) < 6) {
            $pwError = "New password must be at least 6 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $pwError = "New password and confirmation do not match.";
        } elseif (password_verify($newPassword, $userRow['password'])) {
            $pwError = "New password must be different from the current password.";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $newHash, $userId);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $userId, 'password_changed', 'Changed account password');
            $pwSuccess = "Password changed successfully.";
        }
    }
}

// Fetch current profile (after any updates above)
$profile = $conn->query("SELECT * FROM profile WHERE user_id = $userId")->fetch_assoc();

$activePage = 'profile';
$pageTitle = 'Profile Management';
include 'includes/head.php';
?>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/flash.php'; ?>

        <div class="row g-3">
            <!-- Profile Image Upload -->
            <div class="col-lg-4">
                <div class="panel text-center fade-in-up">
                    <h5 class="mb-3">Profile Image</h5>
                    <?php
                        $imgPath = 'assets/uploads/' . $profile['profile_image'];
                        $imgExists = $profile['profile_image'] !== 'default.png' && file_exists($imgPath);
                    ?>
                    <img src="<?php echo $imgExists ? htmlspecialchars($imgPath) : 'https://ui-avatars.com/api/?name=' . urlencode($profile['full_name']) . '&size=110'; ?>"
                         class="profile-img-preview mb-3" alt="Profile picture of <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <input type="file" name="profile_image" class="form-control mb-2" accept=".jpg,.jpeg,.png,.webp" required>
                        <button type="submit" name="upload_image" class="btn btn-primary btn-sm w-100">Upload Image</button>
                    </form>
                </div>

                <!-- Resume Upload (Week 6 - Professional Contact Experience) -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Resume</h5>
                    <?php if (!empty($profile['resume_file']) && file_exists('assets/uploads/' . $profile['resume_file'])): ?>
                        <a href="assets/uploads/<?php echo htmlspecialchars($profile['resume_file']); ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="bi bi-file-earmark-pdf"></i> View Current Resume
                        </a>
                    <?php else: ?>
                        <p class="text-muted small">No resume uploaded yet. Once added, a Download Resume button appears on your live portfolio.</p>
                    <?php endif; ?>
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <input type="file" name="resume_file" class="form-control mb-2" accept=".pdf" required>
                        <button type="submit" name="upload_resume" class="btn btn-primary btn-sm w-100">Upload Resume (PDF)</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Change Password</h5>
                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" required>
                            <div class="form-text">At least 6 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary btn-sm w-100">Update Password</button>
                    </form>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="col-lg-8">
                <div class="panel fade-in-up">
                    <h5 class="mb-3">Personal Information</h5>
                    <form method="POST" action="profile.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Job Title</label>
                                <input type="text" name="job_title" class="form-control" value="<?php echo htmlspecialchars($profile['job_title']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($profile['location']); ?>">
                            </div>
                            <div class="col-12"><hr class="my-1"><small class="text-muted">Social Links (shown on your live portfolio)</small></div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-linkedin"></i> LinkedIn</label>
                                <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/..." value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-github"></i> GitHub</label>
                                <input type="url" name="github_url" class="form-control" placeholder="https://github.com/..." value="<?php echo htmlspecialchars($profile['github_url'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-twitter-x"></i> Twitter / X</label>
                                <input type="url" name="twitter_url" class="form-control" placeholder="https://x.com/..." value="<?php echo htmlspecialchars($profile['twitter_url'] ?? ''); ?>">
                            </div>
                        </div>
                        <button type="submit" name="update_info" class="btn btn-primary mt-3">Save Changes</button>
                    </form>
                </div>

                <!-- About Section -->
                <div class="panel fade-in-up">
                    <h5 class="mb-3">About Section</h5>
                    <form method="POST" action="profile.php">
                        <textarea name="about_text" rows="5" class="form-control"><?php echo htmlspecialchars($profile['about_text']); ?></textarea>
                        <button type="submit" name="update_about" class="btn btn-primary mt-3">Save About</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
