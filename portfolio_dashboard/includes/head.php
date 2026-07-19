<?php
// includes/head.php - shared <head> + opening <body> partial
// Expects (optional, set by the including page BEFORE this include):
//   $pageTitle  - used in the <title> tag (falls back to 'Portfolio Admin')
//   $extraCss   - array of additional stylesheet paths (e.g. preview.css)
//   $bodyClass  - class(es) to add to <body> (e.g. login page needs 'login-body')
$pageTitle = $pageTitle ?? 'Portfolio Admin';
$extraCss = $extraCss ?? [];
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Portfolio Admin</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php foreach ($extraCss as $cssFile): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssFile); ?>">
    <?php endforeach; ?>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">
