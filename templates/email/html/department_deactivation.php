<h2><?= h(__('Compte rendu de désactivation de département')) ?></h2>

<p><?= h(__('Départements traités : {0}.', implode(', ', $report['department_ids'] ?? []))) ?></p>

<ul>
    <li><?= h(__('Utilisateurs détachés : {0}', count($report['detached_user_ids'] ?? []))) ?></li>
    <li><?= h(__('Séquences désactivées : {0}', count($report['deactivated_sequence_ids'] ?? []))) ?></li>
    <li><?= h(__('Codes CGR désactivés : {0}', count($report['deactivated_cgr_ids'] ?? []))) ?></li>
    <li><?= h(__('Références CGR par défaut supprimées : {0}', count($report['cleared_default_department_ids'] ?? []))) ?></li>
</ul>

<?php if ($fullReport): ?>
    <p><?= h(__('Identifiants traités :')) ?></p>
    <pre><?= h(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
<?php else: ?>
    <p><?= h(__('Votre rattachement à un département a été supprimé. Votre compte reste actif.')) ?></p>
<?php endif; ?>
