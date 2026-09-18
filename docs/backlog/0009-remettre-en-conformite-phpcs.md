# 0009 — Remettre l’application en conformité PHP_CodeSniffer

**Statut :** Terminé

**Priorité :** Haute

## Prompt Codex

Reprends la remise en conformité PHP_CodeSniffer. Lis `AGENTS.md`, puis ce
ticket et l’ADR 0053. Vérifie `git status` à la racine et dans `app/`, puis
exécute `make cs.check`. Isole un petit lot cohérent de fichiers qui ne
chevauche pas des modifications locales existantes ; corrige uniquement le
style, les imports, les PHPDoc et les signatures sans changer le comportement
métier, le schéma, les routes ou les contrats API. Exécute les tests adaptés
au lot, `make test.style`, `make cs.check` et `make stan`. Ne crée ni branche,
ni commit, ni pull request sans accord explicite.

## Contexte

La commande `make cs.check` relève actuellement 208 erreurs et 83 avertissements PHP_CodeSniffer sur 43 fichiers. Les écarts sont principalement des défauts de style, de documentation PHPDoc, d’imports et de signatures de types. Ils empêchent le contrôle qualité global de réussir, malgré des tests applicatifs verts.

Le chantier ne doit pas être mélangé aux évolutions fonctionnelles en cours, en particulier celles concernant TreeBehavior, le workflow et les menus. L’exécution globale de `make cs.fix` est exclue tant que ces modifications ne sont pas isolées : elle modifierait de nombreux fichiers et compliquerait la revue.

## Objectif

Créer une branche dédiée `refactor/phpcs-conformity` afin de supprimer l’ensemble des erreurs et avertissements relevés par PHP_CodeSniffer, sans modifier le comportement métier, le schéma, les routes ou les contrats API.

## Travaux à réaliser

- Établir et conserver l’état initial reproductible avec `make cs.check`.
- Corriger les fichiers par lots cohérents : Models/Tables et entités, commandes et services, policies et vues, puis contrôleurs Web/API et mailers.
- Employer `phpcbf` uniquement sur une sélection de fichiers révisée ; traiter manuellement les PHPDoc, imports, types natifs et structures de contrôle.
- Réduire aussi les avertissements de longueur de ligne afin que le rapport final soit vide.
- Vérifier que `vendor/bin/phpstan` est disponible dans le conteneur avant d’exécuter la vérification statique requise.

## Critères d’acceptation

- `make cs.check` réussit sans erreur ni avertissement.
- Les tests adaptés aux fichiers traités restent verts : au minimum `make test.unit`, `make test.style` et `make stan`.
- `make test.integration` est exécuté si des Models, Tables, finders ou requêtes SQL sont modifiés.
- `make test.api` est exécuté si des contrôleurs Web ou API sont modifiés.
- Les corrections restent purement structurelles ; toute modification fonctionnelle découverte pendant le chantier fait l’objet d’un ticket ou d’une décision séparée.
- Chaque lot conserve un diff lisible et ne contient aucun changement appartenant aux évolutions Tree, workflow ou menus en cours.

## Références

- [Configuration PHP_CodeSniffer](../../phpcs.xml)
- [ADR 0032 — Standardisation du flux de travail de développement](../adr/0032-flux-de-travail-developpement.md)
- [ADR 0053 — Stratégie de tests unitaires et intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)
