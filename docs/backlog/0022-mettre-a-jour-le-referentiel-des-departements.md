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

## État de l'existant

Une base technique d'import est déjà disponible dans
`MigrationImportDepartmentsCommand` et `DepartmentImportService` :

- lecture des exports historiques de sociétés, services et codes CGR, avec
  préparation d'un plan déterministe ;
- simulation sans accès à la base avec `--dry-run` et rapport CSV des services
  non rapprochés avec `--report-dir` ;
- application transactionnelle des sociétés, services et associations CGR
  avec `--apply` ;
- rapprochement des départements par code, conservation de l'identifiant
  lorsqu'un code existe déjà et refus si son parent diffère ;
- tests du plan d'import, des doublons et de la simulation sans base.

Cette commande est un point de départ, pas une reprise complète du référentiel
visé par ce ticket. Elle ne produit pas le rapport détaillé avant/après exigé,
ne traite pas les changements de code, déplacements ou désactivations, et ne
met pas à jour les affectations `user_departments` ni les associations de
responsables. Ses tests ne vérifient pas l'application transactionnelle ni
l'intégrité de l'arbre après import. Aucun export métier ni liste cible
validée de SEM/Rédac n'est présent dans le dépôt. Les questions
Q-0022-01 à Q-0022-04 restent à poser ; le statut demeure donc `À planifier`.

## Travail attendu

- [ ] Identifier et faire valider la source de vérité du nouvel arbre et la
  correspondance des départements existants.
- [ ] Produire un état initial exporté et un plan de transformation avant
  toute modification.
- [ ] Recenser les départements fils de `SEM/Rédac`, leurs codes, libellés,
  parents, responsables, stratégies CGR et affectations utilisateurs.
- [ ] Définir le traitement des créations, renommages, déplacements,
  désactivations et doublons sans se baser uniquement sur le libellé.
- [ ] Compléter la procédure d'import existante pour le référentiel validé :
  mode `dry-run` détaillant les ajouts, modifications, déplacements et
  désactivations, rapport des anomalies et comparaison avant/après, puis
  application transactionnelle. La commande historique décrite dans l'état de
  l'existant ne couvre qu'une partie de ce besoin.
- [ ] Préserver les identifiants et les relations lorsqu'une correspondance
  fiable existe ; documenter toute rupture nécessaire.
- [ ] Vérifier l'intégrité de l'arbre, l'absence de boucle et les références
  aux utilisateurs, CGR, DAE et workflows.
- [ ] Mettre à jour les associations `user_departments` et les responsables
  uniquement à partir de la source validée.
- [ ] Compléter les tests d'import pour couvrir l'application transactionnelle,
  l'intégrité de l'arbre et les contrôles de non-régression ; les tests
  existants couvrent la préparation des CSV et le mode simulation.

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
