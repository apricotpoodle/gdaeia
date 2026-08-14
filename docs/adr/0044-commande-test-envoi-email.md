# ADR 0044 : Commande CLI pour le test d'envoi de courriels

**Date :** 14 Août 2026  
**Statut :** Accepté

## Contexte
Afin de tester l'intégration SMTP et le fonctionnement du `UserMailer` sans dépendre d'une action Web ou d'un formulaire front-end, nous avons besoin d'un outil de diagnostic en ligne de commande.

## Décision
1. Création de la commande `src/Command/TestEmailCommand.php`.
2. Enregistrement explicite du nom court `test_email` dans `Application::console()`.
3. Utilisation de la méthode `safeSend()` issue d' `AppMailer` / `EmailLoggerTrait` pour garantir la capture des erreurs dans `logs/email.log`.

## Justification (KISS & DRY)
Permet de valider rapidement l'infrastructure réseau et les paramètres `EmailTransport` en environnement de développement ou de recette.