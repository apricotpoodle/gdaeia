# 0036 — Ajouter le compteur des demandes à valider

**Statut :** Terminé

**Priorité :** Moyenne — repérage des validations en attente

**Dépendance :** [0035 — Compléter les notifications GDAE](0035-completer-notifications-gdae.md).

## Objectif

Afficher à chaque valideur le nombre de demandes qu'il peut réellement
valider, et permettre de filtrer la grille sur ces demandes.

## Travail attendu

- [x] Calculer le compteur côté serveur en réutilisant les Policies, le
  workflow et les périmètres existants.
- [x] Ajouter le filtre de grille avec pagination, tri et filtrage distants
  conservés côté serveur.
- [x] Ajouter le bouton ou indicateur via les fabriques et mécanismes
  Tabulator existants.
- [x] Couvrir les rôles parallèles, les demandes clôturées, les demandes hors
  périmètre et les utilisateurs sans tâche.

## Critères d'acceptation

1. Le compteur égale le nombre de DAE actuellement accessibles à la décision
   de l'identité courante.
2. Le filtre retourne exactement ces demandes et le serveur reste l'autorité
   sur l'accès aux données.
3. Un utilisateur ne peut ni gonfler le compteur ni lire une DAE en forgeant
   les paramètres du filtre.
4. Le compteur se met à jour après une transition de validation pertinente.

## Vérifications

- `make test.unit`
- `make test.api` pour compteur, Policy et grille distante
- `make test.integration` pour le finder ORM et les périmètres
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0004 — API Tabulator et patrons](../adr/0004-api-tabulator-et-design-patterns.md)
- [ADR 0014 — Fonctionnalités Tabulator](../adr/0014-standardisation-fonctionnalites-tabulator.md)
- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- `src/Service/Workflow/ApplicationformValidationWorkflow.php`
