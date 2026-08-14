# ADR 0043 : Commande CLI de test d'envoi de courriels et serveur d'interception Mailpit

**Date :** 14 Août 2026  
**Statut :** Accepté

## Contexte
Afin d'éprouver la chaîne d'expédition des courriels transactionnels (`UserMailer` et `AppMailer`) en environnement de développement, nous devons pouvoir tester l'envoi sans dépendre d'une action IHM Web et sans risquer d'expédier de véritables courriels sur Internet.

## Décision
1. **Serveur d'interception Dev** : Intégration du service `mailpit` dans `docker-compose.yml` (`axllent/mailpit`), exposant l'IHM Web sur le port `8025` et l'écoute SMTP sur le port `1025`.
2. **Configuration réseau & local** : Mise à jour du fichier `config/app_local.php` pour utiliser le driver `SmtpTransport` pointant vers le conteneur `mailpit:1025`.
3. **Outillage CLI** : Création de la commande `src/Command/TestEmailCommand.php` déclarée dans `src/Application.php` pour exécuter des tests unitaires d'envoi via `bin/cake test_email <email>`.
4. **Journalisation** : Utilisation du `EmailLoggerTrait` via `AppMailer` pour consigner le suivi d'expédition dans `logs/email.log`.

## Justification (Principes SOLID & 12-Factor App)
* **Isolation** : Aucun courriel ne quitte l'infrastructure Docker locale.
* **Developer Experience (DX)** : Visualisation immédiate des rendus HTML, texte brut et en-têtes dans l'interface Mailpit (`http://localhost:8025`).
* **Portabilité** : Le code applicatif (`UserMailer`, `TestEmailCommand`) reste agnostique de l'environnement ; le passage en production s'effectuera par simple bascule des variables d'environnement SMTP sans toucher au code.