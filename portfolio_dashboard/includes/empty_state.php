<?php
// includes/empty_state.php - Reusable "nothing here yet" block.
// Expects the including page to set $emptyIcon, $emptyTitle, $emptyText BEFORE including,
// and optionally $emptyActionUrl + $emptyActionLabel for a call-to-action button.
// Unset these after use so they don't leak into the next empty state on the same page.

$emptyIcon = $emptyIcon ?? 'inbox';
$emptyTitle = $emptyTitle ?? 'Nothing here yet';
$emptyText = $emptyText ?? '';
$emptyActionUrl = $emptyActionUrl ?? '';
$emptyActionLabel = $emptyActionLabel ?? '';
?>
<div class="empty-state text-center py-5">
    <i class="bi bi-<?php echo htmlspecialchars($emptyIcon); ?>"></i>
    <h6 class="mt-3 mb-1"><?php echo htmlspecialchars($emptyTitle); ?></h6>
    <?php if ($emptyText !== ''): ?>
        <p class="text-muted small mb-3"><?php echo htmlspecialchars($emptyText); ?></p>
    <?php endif; ?>
    <?php if ($emptyActionUrl !== '' && $emptyActionLabel !== ''): ?>
        <?php if (strpos($emptyActionUrl, 'modal:') === 0): ?>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#<?php echo htmlspecialchars(substr($emptyActionUrl, 6)); ?>">
                <?php echo htmlspecialchars($emptyActionLabel); ?>
            </button>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($emptyActionUrl); ?>" class="btn btn-primary btn-sm"><?php echo htmlspecialchars($emptyActionLabel); ?></a>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php
// Reset so a second empty-state on the same page doesn't inherit these values.
$emptyIcon = null; $emptyTitle = null; $emptyText = null; $emptyActionUrl = null; $emptyActionLabel = null;
?>
