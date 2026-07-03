# ADR 0001 : Choix de l'infrastructure Docker pour le développement

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
Pour développer notre application CakePHP 5.3 ex nihilo, nous avons besoin d'un environnement de développement local reproductible, isolé et facile à partager, sans polluer la machine hôte.

## Décision
Nous avons décidé d'utiliser Docker avec **Docker Compose**.
L'image de base choisie pour l'application est `php:8.3-apache` construite sur mesure (pas d'image tierce boîte noire).

## Justification
1. **Contrôle et Sécurité :** Partir d'une image PHP officielle nous permet d'installer uniquement les extensions requises par CakePHP 5.3, respectant le principe KISS.
2. **Simplicité du Serveur Web :** L'utilisation d'Apache intégré simplifie le routage par défaut de CakePHP (utilisation des `.htaccess` natifs) comparativement à un couplage Nginx + PHP-FPM.
3. **Prévention des conflits de droits :** Le build intègre une redéfinition de l'UID/GID de `www-data` pour correspondre à l'utilisateur hôte, résolvant le problème classique des fichiers générés appartenant à `root`.

## Conséquences
* Le développement nécessite l'installation de Docker, Docker Compose et Make sur la machine hôte.
* Le code applicatif (`/app`) est totalement découplé de l'infrastructure (volume monté), ce qui permet de versionner les deux indépendamment si besoin.
