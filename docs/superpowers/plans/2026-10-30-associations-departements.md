# Associations multiples des départements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Remplacer les rattachements uniques des responsables et des codes CGR par des associations multiples administrables et sécurisées.

**Architecture:** Introduire deux tables de liaison Many-to-Many, migrer les données dans une migration MySQL transactionnelle, puis adapter l'ORM, les services, l'API et l'IHM par incréments testés. Les Policies et les custom finders restent les points d'autorité.

**Tech Stack:** CakePHP 5.3, ORM CakePHP, Phinx, MySQL/InnoDB, PHPUnit, Tabulator et modules JavaScript ES6.

**Spec:** `docs/adr/0072-associations-multiples-departements-cgr.md` et `docs/backlog/0024-generaliser-les-associations-des-departements.md`

## Global Constraints

- Ne pas modifier la migration initiale ; ajouter une migration horodatée.
- Exécuter les validations PHP via le Makefile racine et la base `daetf2_test`.
- Ne jamais conserver de lecture ou de libellé métier lié à un CGR par défaut.
- Utiliser des alias ORM explicites pour les relations multiples.
- Protéger toutes les mutations par les Policies et les autorisations de champs.

### Task 1: Schéma et reprise des données

**Files:**
- Create: `app/config/Migrations/20261030120000_NormalizeDepartmentAssociations.php`
- Modify: `app/docs/adr/README.md`
- Create: `app/docs/adr/0072-associations-multiples-departements-cgr.md`
- Test: tests d'intégration de migration et contrôle SQL MySQL

- [x] Écrire les tests de structure, de clés composites, d'unicité globale et de reprise des anciennes associations.
- [x] Vérifier l'échec attendu sur le schéma historique.
- [x] Créer les tables et transférer les données dans une migration MySQL reprenable.
- [x] Vérifier les contraintes, doublons et colonnes supprimées.

### Task 2: ORM, entités et résolveur CGR

**Files:**
- Modify: `app/src/Model/Table/DepartmentsTable.php`
- Modify: `app/src/Model/Table/CgrCodesTable.php`
- Modify: `app/src/Model/Entity/Department.php`
- Modify: `app/src/Model/Entity/CgrCode.php`
- Modify: `app/src/Service/CgrResolverService.php`

- [x] Tester les collections `Managers` et `CgrCodes` et l'unicité globale.
- [x] Remplacer les associations directes par les Many-to-Many et leurs entités de liaison.
- [x] Supprimer les constantes, champs accessibles et validations obsolètes.
- [x] Résoudre les codes actifs via la liaison département/CGR.

### Task 3: Service métier et API

**Files:**
- Modify: `app/src/Service/DepartmentManagementService.php`
- Modify: `app/src/Controller/Api/DepartmentsController.php`
- Modify: `app/src/Controller/DepartmentsController.php`
- Test: tests unitaires et fonctionnels des mutations

- [x] Tester l'enregistrement atomique des deux collections et la dissociation sans suppression d'entité.
- [x] Adapter les payloads `manager_ids` et `cgr_code_ids` et les réponses JSON en français.
- [x] Séparer les routes du catalogue CGR des routes d'association.
- [x] Conserver les autorisations et les réponses 422.

### Task 4: IHM, fixtures et documentation

**Files:**
- Modify: `app/templates/Departments/form.php`
- Modify: `app/webroot/js/views/Departments/form.js`
- Modify: fixtures et README locaux concernés

- [x] Tester les états vides, doublons et codes inactifs.
- [x] Remplacer les champs singuliers par des sélections multiples.
- [x] Supprimer les textes et filtres liés au CGR par défaut.
- [x] Mettre à jour les fixtures et la documentation.

### Task 5: Validation finale

- [x] Exécuter `make test.unit`, `make test.integration`, `make test.api`, `make test.workflow`, `make test.style`, `make cs.check`, `make stan` et `make test.all`.
- [x] Vérifier les associations avec le REPL CakePHP selon l'ADR 0010.
- [x] Vérifier le diff final et l'absence de secrets ou fichiers générés.

