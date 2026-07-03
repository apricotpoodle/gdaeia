# ADR 0011 : Droits sur le répertoire personnel de www-data

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'exécution d'outils CLI interactifs (comme `bin/cake console` basé sur PsySH) ou d'outils globaux (Composer) au sein du conteneur lève des avertissements de type `Notice: Writing to directory /var/www/.config/psysh is not allowed`. 

## Décision
Le `Dockerfile` est modifié pour attribuer la propriété de l'intégralité du répertoire `/var/www` (le dossier personnel par défaut sous Debian) à l'utilisateur `www-data` remappé, et non plus uniquement au sous-dossier applicatif `/var/www/html`.

## Justification
Cette modification permet aux utilitaires CLI exécutés sous l'identité `www-data` d'écrire leurs fichiers de configuration et d'historique (fichiers dot) sans entrave, garantissant un environnement de développement silencieux et pleinement fonctionnel.