<?php
/** @var object $recipient */
/** @var list<\App\Service\Workflow\BlockedValidationCycle> $blockedCycles */
/** @var int $count */
/** @var string $baseUrl */
?>
<p><?= __('Bonjour {0},', h($recipient->display_name ?? $recipient->email)) ?></p>
<p><?= __('{0} DAE sont concernées par un cycle de validation bloqué.', $count) ?></p>
<ul>
<?php foreach ($blockedCycles as $cycle): ?>
    <li>
        <strong><?= h($cycle->applicationformNumber) ?></strong> — <?= h($cycle->beginAt?->format('d/m/Y') ?? '—') ?> — <?= h($cycle->department->name) ?><br>
        <?= h(implode(', ', array_map(static fn ($step): string => $step->role->name, $cycle->blockedSteps))) ?>,
        <?= h($cycle->businessDays) ?> <?= __('jours ouvrés') ?>.
        <a href="<?= h($baseUrl . $cycle->url) ?>"><?= __('Ouvrir la validation') ?></a>
    </li>
<?php endforeach; ?>
</ul>
