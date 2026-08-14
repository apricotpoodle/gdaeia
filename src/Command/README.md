# Module Commandes CLI (`src/Command`)

Ce répertoire contient les commandes en ligne de commande (CLI) accessibles via l'exécutable `bin/cake`.

## Commandes disponibles

* **`bin/cake test_email <email>`** : Génère un utilisateur fictif et teste l'expédition d'un courriel transactionnel via `UserMailer` (s'appuie sur `AppMailer::safeSend()`).

## Différences Dev / Prod
* **Dev** : Courriels interceptés localement par Mailpit (`http://localhost:8025`).
* **Prod** : Routage vers le serveur SMTP officiel via la configuration `app_local.php` / `.env`.

## ADR Associés
* [ADR 0044 : Commande CLI de test d'envoi de courriels et serveur Mailpit](../../docs/adr/0044-commande-test-envoi-email.md)