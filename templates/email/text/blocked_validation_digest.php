<?php
/** @var object $recipient */
/** @var list<\App\Service\Workflow\BlockedValidationCycle> $blockedCycles */
/** @var int $count */
/** @var string $baseUrl */
?>
<?= __('Bonjour {0},', $recipient->display_name ?? $recipient->email) ?>

<?= __('{0} DAE sont concernées par un cycle de validation bloqué.', $count) ?>
<?php foreach ($blockedCycles as $cycle): ?>

<?= $cycle->applicationformNumber ?> — <?= $cycle->beginAt?->format('d/m/Y') ?? '—' ?> — <?= $cycle->department->name ?>
<?= implode(', ', array_map(static fn ($step): string => $step->role->name, $cycle->blockedSteps)) ?> — <?= $cycle->businessDays ?> <?= __('jours ouvrés') ?>
<?= $baseUrl . $cycle->url ?>
<?php endforeach; ?>
