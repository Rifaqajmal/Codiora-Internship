<?php
// preview.php - Public-facing live portfolio page.
// No auth required: this is what a visitor/recruiter would see.
// Always pulls fresh data directly from the database, so dashboard changes show immediately.

require_once 'includes/db.php';

// For a single-admin portfolio, show user_id = 1's data.
// (If you add multi-user support later, pass ?user=ID in the query string.)
$userId = (int) ($_GET['user'] ?? 1);

$profile = $conn->query("SELECT * FROM profile WHERE user_id = $userId")->fetch_assoc();

if (!$profile) {
    http_response_code(404);
    die("Portfolio not found.");
}

$skills = $conn->query("SELECT * FROM skills WHERE user_id = $userId ORDER BY category, skill_name");
$skillsByCategory = [];
while ($s = $skills->fetch_assoc()) {
    $skillsByCategory[$s['category']][] = $s;
}

// Filters for projects (GET, no page reload via JS for category, but works without JS too)
$filterCategory = trim($_GET['pcategory'] ?? '');
$projQuery = "SELECT p.*, c.category_name FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.user_id = ?";
$projParams = [$userId];
$projTypes = 'i';
if ($filterCategory !== '') {
    $projQuery .= " AND c.category_name = ?";
    $projParams[] = $filterCategory;
    $projTypes .= 's';
}
$projQuery .= " ORDER BY p.created_at DESC";
$stmt = $conn->prepare($projQuery);
$stmt->bind_param($projTypes, ...$projParams);
$stmt->execute();
$projects = $stmt->get_result();

$allProjects = [];
while ($row = $projects->fetch_assoc()) {
    $allProjects[] = $row;
}

$categories = $conn->query("SELECT DISTINCT c.category_name FROM categories c
    INNER JOIN projects p ON p.category_id = c.id WHERE p.user_id = $userId");
$categoryNames = [];
while ($c = $categories->fetch_assoc()) {
    $categoryNames[] = $c['category_name'];
}

$imgPath = 'assets/uploads/' . $profile['profile_image'];
$profileImgSrc = ($profile['profile_image'] !== 'default.png' && file_exists($imgPath))
    ? $imgPath
    : 'https://ui-avatars.com/api/?name=' . urlencode($profile['full_name']) . '&size=200';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['full_name']); ?> - Portfolio</title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_strimwidth($profile['about_text'], 0, 150, '...')); ?>">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">

    <!-- Open Graph tags for link previews on LinkedIn, WhatsApp, etc. -->
    <meta property="og:title" content="<?php echo htmlspecialchars($profile['full_name']); ?> - Portfolio">
    <meta property="og:description" content="<?php echo htmlspecialchars(mb_strimwidth($profile['about_text'], 0, 150, '...')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo htmlspecialchars($profileImgSrc); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/preview.css">
</head>
<body>

<!-- Skip link for keyboard/screen-reader users (accessibility) -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Hero / About -->
<header class="hero" role="banner">
    <div class="container text-center">
        <img src="<?php echo htmlspecialchars($profileImgSrc); ?>" alt="Photo of <?php echo htmlspecialchars($profile['full_name']); ?>" class="hero-img mb-3" loading="lazy">
        <h1><?php echo htmlspecialchars($profile['full_name']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($profile['job_title']); ?></p>
        <p class="text-muted">
            <i class="bi bi-geo-alt" aria-hidden="true"></i> <?php echo htmlspecialchars($profile['location']); ?>
        </p>
        <nav aria-label="Contact links" class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
            <?php if ($profile['email']): ?>
                <a href="mailto:<?php echo htmlspecialchars($profile['email']); ?>" class="btn btn-outline-light btn-sm" aria-label="Email <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-envelope" aria-hidden="true"></i> Email
                </a>
            <?php endif; ?>
            <?php if ($profile['phone']): ?>
                <a href="tel:<?php echo htmlspecialchars($profile['phone']); ?>" class="btn btn-outline-light btn-sm" aria-label="Call <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-telephone" aria-hidden="true"></i> Call
                </a>
            <?php endif; ?>
            <?php if (!empty($profile['linkedin_url'])): ?>
                <a href="<?php echo htmlspecialchars($profile['linkedin_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm" aria-label="LinkedIn profile of <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-linkedin" aria-hidden="true"></i> LinkedIn
                </a>
            <?php endif; ?>
            <?php if (!empty($profile['github_url'])): ?>
                <a href="<?php echo htmlspecialchars($profile['github_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm" aria-label="GitHub profile of <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-github" aria-hidden="true"></i> GitHub
                </a>
            <?php endif; ?>
            <?php if (!empty($profile['twitter_url'])): ?>
                <a href="<?php echo htmlspecialchars($profile['twitter_url']); ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm" aria-label="Twitter/X profile of <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-twitter-x" aria-hidden="true"></i> Twitter
                </a>
            <?php endif; ?>
            <?php if (!empty($profile['resume_file']) && file_exists('assets/uploads/' . $profile['resume_file'])): ?>
                <a href="assets/uploads/<?php echo htmlspecialchars($profile['resume_file']); ?>" download class="btn btn-light btn-sm" aria-label="Download resume of <?php echo htmlspecialchars($profile['full_name']); ?>">
                    <i class="bi bi-download" aria-hidden="true"></i> Download Resume
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="main-content">
    <!-- About -->
    <section class="section fade-in-up" aria-labelledby="about-heading">
        <div class="container">
            <h2 id="about-heading">About Me</h2>
            <p><?php echo nl2br(htmlspecialchars($profile['about_text'])); ?></p>
        </div>
    </section>

    <!-- Skills -->
    <section class="section bg-light fade-in-up" aria-labelledby="skills-heading">
        <div class="container">
            <h2 id="skills-heading">Skills</h2>
            <?php if (empty($skillsByCategory)): ?>
                <p class="text-muted">Skills will appear here once added.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($skillsByCategory as $category => $skillList): ?>
                        <div class="col-md-6">
                            <h5><?php echo htmlspecialchars($category); ?></h5>
                            <?php foreach ($skillList as $sk): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars($sk['skill_name']); ?></span>
                                        <span class="text-muted small"><?php echo $sk['proficiency']; ?>%</span>
                                    </div>
                                    <div class="skill-bar-bg" role="progressbar" aria-valuenow="<?php echo $sk['proficiency']; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo htmlspecialchars($sk['skill_name']); ?> proficiency">
                                        <div class="skill-bar-fill" style="width: <?php echo $sk['proficiency']; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Projects -->
    <section class="section fade-in-up" aria-labelledby="projects-heading">
        <div class="container">
            <h2 id="projects-heading">Projects</h2>

            <!-- Category filter (no page reload needed thanks to JS below; works without JS too via GET) -->
            <div class="filter-bar mb-4" role="group" aria-label="Filter projects by category">
                <button class="filter-btn active" data-category="" aria-pressed="true">All</button>
                <?php foreach ($categoryNames as $cat): ?>
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($cat); ?>" aria-pressed="false">
                        <?php echo htmlspecialchars($cat); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="row g-4" id="projectGrid">
                <?php if (empty($allProjects)): ?>
                    <p class="text-muted">Projects will appear here once added.</p>
                <?php endif; ?>
                <?php foreach ($allProjects as $p): ?>
                    <?php
                        $pImgPath = 'assets/uploads/' . $p['project_image'];
                        $pImgExists = $p['project_image'] !== 'default_project.png' && file_exists($pImgPath);
                        $pImgSrc = $pImgExists ? $pImgPath : 'https://placehold.co/400x250?text=' . urlencode($p['title']);
                    ?>
                    <div class="col-md-4 project-item" data-category="<?php echo htmlspecialchars($p['category_name'] ?? ''); ?>">
                        <article class="project-card-public h-100">
                            <img src="<?php echo htmlspecialchars($pImgSrc); ?>" alt="Screenshot of <?php echo htmlspecialchars($p['title']); ?>" loading="lazy">
                            <div class="p-3">
                                <h3 class="h6"><?php echo htmlspecialchars($p['title']); ?></h3>
                                <p class="small text-muted"><?php echo htmlspecialchars(mb_strimwidth($p['description'], 0, 100, '...')); ?></p>
                                <?php if ($p['technology']): ?>
                                    <div class="mb-2">
                                        <?php foreach (explode(',', $p['technology']) as $tech): ?>
                                            <span class="tech-badge"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex gap-2">
                                    <?php if ($p['project_link']): ?>
                                        <a href="<?php echo htmlspecialchars($p['project_link']); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary" aria-label="View live demo of <?php echo htmlspecialchars($p['title']); ?>">Live</a>
                                    <?php endif; ?>
                                    <?php if ($p['github_link']): ?>
                                        <a href="<?php echo htmlspecialchars($p['github_link']); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark" aria-label="View source code of <?php echo htmlspecialchars($p['title']); ?> on GitHub">GitHub</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section class="section bg-light fade-in-up" aria-labelledby="contact-heading">
        <div class="container" style="max-width:600px;">
            <h2 id="contact-heading">Get In Touch</h2>
            <form id="contactForm" novalidate>
                <div class="mb-3">
                    <label for="contact_name" class="form-label">Name <span aria-hidden="true">*</span></label>
                    <input type="text" id="contact_name" name="name" class="form-control" required aria-required="true">
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>
                <div class="mb-3">
                    <label for="contact_email" class="form-label">Email <span aria-hidden="true">*</span></label>
                    <input type="email" id="contact_email" name="email" class="form-control" required aria-required="true">
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="mb-3">
                    <label for="contact_message" class="form-label">Message <span aria-hidden="true">*</span></label>
                    <textarea id="contact_message" name="message" rows="4" class="form-control" required aria-required="true"></textarea>
                    <div class="invalid-feedback">Please enter a message.</div>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
                <div id="formSuccess" class="alert alert-success mt-3" role="status" style="display:none;">
                    Thanks! Your message has been noted (demo form — not yet wired to email delivery).
                </div>
            </form>
        </div>
    </section>
</main>

<footer class="text-center py-4 text-muted small">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($profile['full_name']); ?>. Built with PHP &amp; MySQL.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/preview.js"></script>
</body>
</html>
