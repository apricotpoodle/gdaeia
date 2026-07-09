# ADR 0034 : Spécialisation et Routage des Logs de Courriels

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
Les envois de courriels transactionnels (via le serveur SMTP) sont sensibles aux défaillances réseau ou de configuration. Actuellement, les erreurs ou succès liés aux envois d'e-mails sont noyés dans les fichiers généraux `error.log` et `debug.log`, ce qui complexifie le diagnostic en production (Séparation des Préoccupations non respectée).

## Décision
1. **Implémentation des Scopes de Log CakePHP** : Création d'un canal de log exclusif `email` dans `config/app.php`.
2. **Isolation** : Ce canal intercepte tous les niveaux de sévérité (de `info` à `error`) à condition que le contexte d'appel contienne l'étiquette `'scope' => ['email']`.
3. **Exclusivité** : Les canaux par défaut (`error` et `debug`) sont explicitement configurés avec `'scopes' => false` afin d'éviter que les messages de courriels ne soient dupliqués dans ces fichiers (principe DRY appliqué aux logs).

## Justification
Cette approche offre un fichier `logs/email.log` propre et dédié. En cas de réclamation d'un utilisateur affirmant "ne pas avoir reçu son e-mail", le support technique dispose d'un point d'entrée unique et chronologique pour retracer le cycle de vie du jeton et l'interaction avec le serveur SMTP, sans être pollué par l'activité HTTP de l'application.
