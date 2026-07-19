<?php
// includes/file_helper.php
// Safely deletes a previously-uploaded file from assets/uploads/, but never touches
// the built-in placeholder defaults (default.png, default_project.png) and never
// deletes anything outside the uploads folder (defends against a stray '../').

function deleteUploadedFile($filename, $defaultName) {
    if (!$filename || $filename === $defaultName) {
        return; // nothing to delete, it's still the default placeholder
    }

    // basename() strips any directory traversal attempt (e.g. "../../config.php")
    $safeName = basename($filename);
    $path = __DIR__ . '/../assets/uploads/' . $safeName;

    if (is_file($path)) {
        @unlink($path);
    }
}
?>
