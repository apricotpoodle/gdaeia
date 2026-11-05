<?php

/**
 * Affiche une valeur de formulaire en lecture seule.
 *
 * @var \App\View\AppView $this
 * @var string $label
 * @var string|null $value
 * @var string $valueClass
 */

$displayValue = $value === null || trim($value) === '' ? '-' : $value;
?>

<div class="col-md-6">
    <span class="text-muted fs-8 d-block fw-semibold"><?= h($label) ?></span>
    <span class="fw-medium text-dark fs-7 <?= h($valueClass ?? '') ?>"><?= h($displayValue) ?></span>
</div>
