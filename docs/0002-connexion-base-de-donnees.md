# ADR 0002 : Gestion de la configuration et connexion MySQL

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'application doit se connecter à une base de données MySQL externe (ex: `websrv31.glm.lan`). Nous devons garantir que les identifiants de connexion ne sont jamais versionnés dans le code source de l'application (`/app`) pour des raisons évidentes de sécurité.

## Décision
Nous appliquons le principe "Config in Environment" de la méthodologie 12-Factor App. 
Les identifiants sont stockés dans le fichier `.env` à la racine de l'infrastructure (ignoré par Git). Docker Compose se charge d'injecter ces variables sous forme de variables d'environnement natives dans le conteneur PHP.

## Justification (Principes SOLID & KISS)
1. **Sécurité :** Le dépôt Git de l'application CakePHP ne contient aucun secret.
2. **KISS :** CakePHP 5 lit nativement la variable `DATABASE_URL` via la fonction `env()`. Aucune bibliothèque supplémentaire (comme `vlucas/phpdotenv`) n'est requise dans le projet CakePHP lui-même, car Docker fait le travail en amont.
3. **Portabilité :** Pour passer de l'environnement de développement à la production, il suffit de changer le fichier `.env` de l'infrastructure, sans toucher au code applicatif.