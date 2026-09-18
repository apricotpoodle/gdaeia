<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $containerName
 * @var mixed $hostName
 * @var mixed $instanceName
 * @var mixed $isTest
 * @var mixed $issueCount
 * @var mixed $reportedAt
 * @var mixed $reports
 */
?>
<h1>Alerte d’intégrité des arbres</h1>
<p>Bonjour,</p>
<?php if ($isTest): ?>
<p><strong>CECI EST UN TEST.</strong> Aucune donnée n’a été contrôlée ni modifiée.</p>
<?php else: ?>
<p>
    Le contrôle automatique des données hiérarchiques a détecté
    <strong><?= h($issueCount) ?> incohérence(s)</strong>. Aucun correctif n’a été exécuté automatiquement.
</p>
<?php endif; ?>

<h2>Contexte technique</h2>
<ul>
    <li>Instance applicative : <strong><?= h($instanceName) ?></strong></li>
    <li>Machine hôte : <strong><?= h($hostName) ?></strong></li>
    <li>Conteneur ayant exécuté le contrôle : <strong><?= h($containerName) ?></strong></li>
    <li>Date du diagnostic : <strong><?= h($reportedAt) ?></strong></li>
</ul>

<h2>Diagnostic</h2>
<?php foreach ($reports as $report): ?>
    <h3>Table <?= h($report['table']) ?> — <?= h($report['node_count']) ?> nœud(s)</h3>
    <p><?= h(count($report['issues'])) ?> incohérence(s) détectée(s).</p>
    <ol>
    <?php foreach ($report['issues'] as $issue): ?>
        <li>
            <strong>[<?= h($issue['code']) ?>]</strong> <?= h($issue['message']) ?>
            <pre><?= h((string)json_encode($issue['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </li>
    <?php endforeach; ?>
    </ol>
<?php endforeach; ?>

<h2>Marche à suivre</h2>
<ol>
    <li>Conserver ce courriel et prévenir l’équipe applicative.</li>
    <li>Se connecter à la machine indiquée ci-dessus et relancer le diagnostic ciblé :</li>
</ol>
<pre>make tree.check.departments
make tree.check.menus</pre>
<p>
    Ne lancez pas de reconstruction tant qu’un cycle <code>parent_id</code> ou un parent inexistant est signalé :
    <code>recover()</code> ne peut pas corriger ces anomalies.
</p>
<p>Le rapport détaillé ci-dessus permet d’identifier les identifiants à corriger avant toute récupération encadrée.</p>
