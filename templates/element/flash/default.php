<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
$class = 'message';
if (!empty($params['class'])) {
    $class .= ' ' . $params['class'];
}
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div class="toast align-items-center text-white bg-success border-0 show shadow-lg auto-toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body fs-6">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $message ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>