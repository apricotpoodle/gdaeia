# 0022 — Mettre à jour le référentiel des départements

**Statut :** À planifier

**Priorité :** Moyenne

## Contexte

Le contenu du référentiel des départements doit être mis à jour, en
particulier les départements fils de `SEM/Rédac`. Cette opération doit
préserver l'arbre hiérarchique et les relations existantes avec les DAE, les
utilisateurs, les responsables, les stratégies CGR et les circuits de
validation.

## Objectif

Reprendre les données des départements à partir d'une source métier validée,
avec une procédure contrôlée, traçable et reproductible, en ciblant d'abord
les enfants de `SEM/Rédac`.

## Travail attendu

- [ ] Identifier et faire valider la source de vérité du nouvel arbre et la
  correspondance des départements existants.
- [ ] Produire un état initial exporté et un plan de transformation avant
  toute modification.
- [ ] Recenser les départements fils de `SEM/Rédac`, leurs codes, libellés,
  parents, responsables, stratégies CGR et affectations utilisateurs.
- [ ] Définir le traitement des créations, renommages, déplacements,
  désactivations et doublons sans se baser uniquement sur le libellé.
- [ ] Réutiliser ou étendre la procédure d'import existante avec un mode
  `dry-run`, un rapport d'anomalies et une transaction d'application.
- [ ] Préserver les identifiants et les relations lorsqu'une correspondance
  fiable existe ; documenter toute rupture nécessaire.
- [ ] Vérifier l'intégrité de l'arbre, l'absence de boucle et les références
  aux utilisateurs, CGR, DAE et workflows.
- [ ] Mettre à jour les associations `user_departments` et les responsables
  uniquement à partir de la source validée.
- [ ] Ajouter les tests d'import et les contrôles de non-régression.

## Critères d'acceptation

1. La liste cible des enfants de `SEM/Rédac` est validée et versionnée ou
   conservée dans un artefact traçable hors des secrets.
2. Un dry-run présente les ajouts, modifications, déplacements,
   désactivations et anomalies avant application.
3. L'application est transactionnelle et peut être interrompue sans laisser
   un arbre partiellement modifié.
4. Aucun département ne possède plusieurs parents, n'est son propre parent
   ou ne crée de boucle dans l'arbre.
5. Les DAE, utilisateurs, managers, codes CGR et règles de workflow restent
   rattachés à des départements cohérents.
6. Les départements hors périmètre `SEM/Rédac` restent inchangés, sauf
   dépendance explicitement validée.
7. Un rapport final permet de comparer l'état avant et après l'opération.

## Vérifications

- `make test.unit`
- `make test.integration`
- `make test.api`
- `make test.workflow` si les données influencent les vues SQL de workflow
- exécution de l'import en `dry-run`, puis en environnement de test MySQL
- contrôle d'intégrité de l'arbre et revue du rapport de transformation

## Références

- [ADR 0064 — Administration de l'arbre des départements](../adr/0064-administration-arbre-departements.md)
- [ADR 0050 — Sémantique des périmètres hiérarchiques](../adr/0050-semantique-perimetres-hierarchiques-utilisateur.md)
- `src/Command/MigrationImportDepartmentsCommand.php`
- `src/Service/Migration/DepartmentImportService.php`
