Bonjour,

<?php if ($isTest): ?>
CECI EST UN TEST. Aucune donnée n’a été contrôlée ni modifiée.
<?php else: ?>
Le contrôle automatique des données hiérarchiques a détecté <?= $issueCount ?> incohérence(s).
Aucun correctif n’a été exécuté automatiquement.
<?php endif; ?>

CONTEXTE TECHNIQUE
- Instance applicative : <?= $instanceName ?>
- Machine hôte : <?= $hostName ?>
- Conteneur ayant exécuté le contrôle : <?= $containerName ?>
- Date du diagnostic : <?= $reportedAt ?>

DIAGNOSTIC
<?php foreach ($reports as $report): ?>

Table <?= $report['table'] ?> — <?= $report['node_count'] ?> nœud(s), <?= count($report['issues']) ?> incohérence(s)
<?php foreach ($report['issues'] as $issue): ?>
- [<?= $issue['code'] ?>] <?= $issue['message'] ?>
  Détails : <?= json_encode($issue['details'], JSON_UNESCAPED_UNICODE) ?>
<?php endforeach; ?>
<?php endforeach; ?>

MARCHE À SUIVRE
1. Conserver ce courriel et prévenir l’équipe applicative.
2. Se connecter à la machine indiquée ci-dessus et relancer le diagnostic ciblé :

   make tree.check.departments
   make tree.check.menus

Ne lancez pas de reconstruction tant qu’un cycle parent_id ou un parent inexistant est signalé :
recover() ne peut pas corriger ces anomalies.

Le rapport détaillé ci-dessus permet d’identifier les identifiants à corriger avant toute récupération encadrée.
