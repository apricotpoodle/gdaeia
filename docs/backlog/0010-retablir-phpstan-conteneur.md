# 0010 — Rétablir PHPStan dans le conteneur applicatif

**Statut :** Terminé

**Priorité :** Haute

## Contexte

La cible `make stan` exécute `./vendor/bin/phpstan analyse` dans le conteneur `gdaetf`. Elle échoue actuellement avec l’erreur suivante :

```text
OCI runtime exec failed: exec failed: unable to start container process:
exec: "./vendor/bin/phpstan": stat ./vendor/bin/phpstan: no such file or directory
```

L’analyse statique ne peut donc pas participer aux contrôles de livraison, alors qu’elle est requise pour toute modification PHP.

## Objectif

Rendre PHPStan disponible de manière reproductible dans l’image et le volume applicatif utilisés par Docker Compose, sans installer de dépendance directement sur l’hôte.

## Travaux à réaliser

- Identifier pourquoi `phpstan/phpstan` n’est pas présent dans `app/vendor/` : dépendance Composer absente, dépendances de développement non installées, volume Docker obsolète ou étape de construction incomplète.
- Vérifier la configuration Composer et les scripts de construction de l’image, sans mettre à jour les dépendances de manière implicite.
- Corriger le mécanisme d’installation afin que `composer install` fournisse systématiquement `vendor/bin/phpstan` dans le conteneur de développement.
- Vérifier l’effet des volumes Docker sur le répertoire `vendor/` et documenter la procédure de réparation locale si un volume existant est en cause.
- Ajouter, si nécessaire, un contrôle explicite et pédagogique dans le Makefile avant l’exécution de PHPStan.

## Critères d’acceptation

- `make stan` exécute PHPStan dans le conteneur sans erreur de fichier introuvable.
- L’installation est reproductible à partir d’un environnement Docker neuf et après `make composer.install`.
- Aucune dépendance n’est installée ou exécutée depuis l’hôte.
- Les dépendances ne sont modifiées que si l’analyse démontre qu’une dépendance déclarée manque ; la justification figure alors dans le commit dédié.
- La documentation d’environnement indique comment diagnostiquer et réparer une absence de binaire Composer dans le conteneur.

## Références

- [Makefile racine](../../../Makefile)
- [Configuration Composer](../../composer.json)
- [ADR 0032 — Standardisation du flux de travail de développement](../adr/0032-flux-de-travail-developpement.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
