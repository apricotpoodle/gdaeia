# 0018 — Sécuriser la saisie des jours ouvrés d'une DAE

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Dans l'interface d'édition d'une DAE, le champ `working_days` peut être saisi
sans repère suffisamment contraignant. La valeur doit rester cohérente avec
la période indiquée par les dates de début et de fin, tout en permettant à
l'opérateur de corriger le nombre de jours effectivement travaillés.

## Objectif

Reprendre l'interface d'édition d'une DAE afin d'afficher et de sécuriser la
saisie du nombre de jours ouvrés dès que la date de fin est renseignée.

## Travail attendu

- [ ] Afficher le champ « Nombre de jours ouvrés » immédiatement après le
  champ de date de fin dans le formulaire d'édition d'une DAE.
- [ ] Calculer automatiquement le nombre de jours ouvrés entre les dates de
  début et de fin, en excluant les samedis et dimanches.
- [ ] Préremplir le champ avec cette valeur calculée lorsque les deux dates
  sont valides.
- [ ] Autoriser la modification manuelle de la valeur par l'opérateur.
- [ ] Limiter la valeur saisie à un entier compris entre zéro et le nombre de
  jours calendaires inclus entre les dates de début et de fin.
- [ ] Recalculer la valeur par défaut et la limite maximale lorsque l'une des
  dates est modifiée.
- [ ] Empêcher l'enregistrement d'une valeur hors limites côté serveur, même
  si la validation JavaScript est contournée.
- [ ] Conserver le comportement attendu pour une période journalière et le
  calcul de la rémunération de base associé.
- [ ] Ajouter les tests JavaScript, unitaires, d'intégration ou HTTP adaptés
  au niveau de chaque règle introduite.

## Critères d'acceptation

1. Après la saisie d'une date de fin valide, le champ « Nombre de jours
   ouvrés » est visible immédiatement sous cette date.
2. Pour une période du lundi au vendredi, la valeur initiale correspond au
   nombre de jours ouvrés calculé ; pour une période incluant un week-end,
   les samedis et dimanches ne sont pas comptés.
3. L'opérateur peut remplacer la valeur calculée par une valeur entière
   valide, au plus égale au nombre de jours calendaires inclus.
4. Une valeur négative, décimale, non numérique ou supérieure au nombre de
   jours calendaires est refusée par l'interface et par l'API.
5. Une modification des dates recalcule la valeur par défaut et la limite
   maximale sans laisser une valeur devenue incohérente.
6. Le calcul du salaire de base pour une rémunération journalière reste
   correct après modification du nombre de jours ouvrés.

## Vérifications

Exécuter les commandes adaptées via le Makefile racine :

- `make test.unit`
- `make test.style` si des tests JavaScript ou PHP sont ajoutés
- `make cs.check` et `make stan` si du PHP est modifié
- `make test.integration` pour la validation ORM
- `make test.api` pour le formulaire ou l'API des DAE

## Références

- `templates/Applicationforms/edit.php`
- `webroot/js/views/Applicationforms/`
- `src/Model/Table/ApplicationformsTable.php`
- `src/Service/SalaryCalculationService.php`
- [ADR 0039 — Défilement infini des grilles](../adr/0039-chargement-progressif-scroll-infini.md)
