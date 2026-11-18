# 0018 — Sécuriser la saisie des jours ouvrés d'une DAE

**Statut :** Terminé

**Priorité :** Haute

## Contexte

Au moment de la création de ce ticket, dans l'interface d'édition d'une DAE,
le champ `working_days` pouvait être saisi sans repère suffisamment
contraignant. La valeur devait rester cohérente avec la période indiquée par
les dates de début et de fin, tout en permettant à l'opérateur de corriger le
nombre de jours effectivement travaillés.

## Objectif

Reprendre l'interface d'édition d'une DAE afin d'afficher et de sécuriser la
saisie du nombre de jours ouvrés dès que la date de fin est renseignée.

## Travail attendu

- [x] Afficher les champs « date de fin » et « nombre de jours ouvrés » ensemble
  lorsque la date de fin est disponible selon le type de contrat.
- [x] Afficher le champ « Nombre de jours ouvrés » immédiatement auprès du
  champ de date de fin dans le formulaire d'édition d'une DAE.
- [x] Calculer automatiquement le nombre de jours ouvrés entre les dates de
  début et de fin, en excluant les samedis et dimanches.
- [x] Préremplir le champ avec cette valeur calculée lorsque les deux dates
  sont valides.
- [x] Autoriser la modification manuelle de la valeur par l'opérateur.
- [x] Limiter la valeur saisie à un entier compris entre zéro et le nombre de
  jours calendaires inclus entre les dates de début et de fin.
- [x] Recalculer la valeur par défaut et la limite maximale lorsque l'une des
  dates est modifiée.
- [x] Empêcher l'enregistrement d'une valeur hors limites côté serveur, même
  si la validation JavaScript est contournée.
- [x] Conserver le comportement attendu pour une période journalière et le
  calcul de la rémunération de base associé.
- [x] Ajouter les tests unitaires, d'intégration et HTTP adaptés
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

## Limites métier et suite

Le ticket couvre le calcul technique hors samedis et dimanches, la saisie
manuelle et les bornes serveur. La visibilité du champ a depuis été décidée :
il apparaît quand les dates sont valides, sauf pour un CDI dont la date de fin
est désactivée. Une période sans jour ouvré est acceptée ; toute valeur zéro
doit déclencher un avertissement visuel avant l'enregistrement et un courriel
après son succès. Lors d'un changement de dates, l'opérateur doit voir la
valeur actuelle et le nouveau calcul avant de décider quoi faire. L'usage de
la valeur calculée avant envoi, l'exclusion des jours fériés et la saisie
manuelle de zéro pour une période avec des jours de semaine restent à trancher.
Les questions Q-0018-02 à Q-0018-04
restent ouvertes et sont des prérequis du ticket
[0030 — Arbitrer le périmètre GDAE V3](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md).
