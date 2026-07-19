<?php
// includes/flash.php - renders auto-dismissing Bootstrap toasts
// Expects (optional) $success / $error / $pwSuccess / $pwError string variables
// already set by the including page during its POST processing.
$success = $success ?? '';
$error = $error ?? '';
$pwSuccess = $pwSuccess ?? '';
$pwError = $pwError ?? '';

$toasts = [
    ['type' => 'success', 'msg' => $success, 'icon' => 'check-circle', 'delay' => 4000],
    ['type' => 'danger',  'msg' => $error,   'icon' => 'exclamation-triangle', 'delay' => 5000],
    ['type' => 'success', 'msg' => $pwSuccess, 'icon' => 'check-circle', 'delay' => 4000],
    ['type' => 'danger',  'msg' => $pwError,   'icon' => 'exclamation-triangle', 'delay' => 5000],
];
?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080;">
    <?php foreach ($toasts as $t): if ($t['msg'] === '') continue; ?>
    <div class="toast align-items-center text-bg-<?php echo $t['type']; ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="<?php echo $t['delay']; ?>" data-bs-autohide="true">
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-<?php echo $t['icon']; ?> me-2"></i><?php echo htmlspecialchars($t['msg']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(function (el) {
        new bootstrap.Toast(el).show();
    });
});
</script>
