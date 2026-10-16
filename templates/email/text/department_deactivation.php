Compte rendu de désactivation de département

Départements traités : <?= implode(', ', $report['department_ids'] ?? []) . PHP_EOL ?>
Utilisateurs détachés : <?= count($report['detached_user_ids'] ?? []) . PHP_EOL ?>
Séquences désactivées : <?= count($report['deactivated_sequence_ids'] ?? []) . PHP_EOL ?>
Codes CGR désactivés : <?= count($report['deactivated_cgr_ids'] ?? []) . PHP_EOL ?>
Références CGR par défaut supprimées : <?= count($report['cleared_default_department_ids'] ?? []) . PHP_EOL ?>

<?php if ($fullReport): ?>
Identifiants traités :
<?= json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL ?>
<?php else: ?>
Votre rattachement à un département a été supprimé. Votre compte reste actif.
<?php endif; ?>
