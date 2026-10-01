<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 * @var string $variant
 */
$message = !isset($params['escape']) || $params['escape'] !== false ? h($message) : $message;
$urgent = $variant === 'danger' || $variant === 'warning';
?>
<div class="toast flash-toast flash-toast--<?= $variant ?> show" data-flash-source="server" data-flash-type="<?= $variant ?>" role="<?= $urgent ? 'alert' : 'status' ?>" aria-live="<?= $urgent ? 'assertive' : 'polite' ?>" aria-atomic="true">
    <div class="flash-toast__body"><?= $message ?></div>
    <button type="button" class="flash-toast__close" aria-label="Fermer">&times;</button>
</div>
