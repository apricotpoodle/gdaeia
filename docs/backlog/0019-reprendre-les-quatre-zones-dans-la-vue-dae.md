# 0019 — Reprendre les quatre zones dans la vue d'une DAE

**Statut :** À planifier

**Priorité :** Moyenne

## Contexte

L'onglet « Détails de la demande » de l'interface de consultation d'une DAE
présente actuellement une organisation différente de celle utilisée pour
l'édition. Les informations sont regroupées en sections générales, ce qui
rend la comparaison entre consultation et saisie moins directe.

## Objectif

Reprendre l'interface de l'onglet « Détails de la demande » afin d'utiliser
la même organisation visuelle et fonctionnelle que l'interface d'édition,
tout en conservant un affichage strictement en lecture seule.

## Travail attendu

- [ ] Réorganiser l'onglet « Détails de la demande » en quatre zones,
  correspondant à l'interface d'édition :
  - Zone 1 — Administration & candidat ;
  - Zone 2 — Caractéristiques du contrat ;
  - Zone 3 — Rémunération & temps de travail ;
  - Zone 4 — Champs réservés RH / administration.
- [ ] Reprendre les titres, l'ordre, la hiérarchie visuelle et les regroupements
  de champs des éléments utilisés par l'édition.
- [ ] Remplacer les champs de saisie par des valeurs affichées en lecture
  seule, sans modifier les droits ni les règles d'autorisation existants.
- [ ] Afficher les valeurs absentes avec une présentation homogène et
  explicite, sans générer d'erreur PHP ou JavaScript.
- [ ] Préserver les onglets Validation et Commentaires ainsi que leurs
  comportements actuels.
- [ ] Conserver les actions disponibles dans l'en-tête selon les Policies et
  les droits de l'utilisateur.
- [ ] Vérifier la lisibilité responsive des quatre zones sur écran large et
  écran réduit.
- [ ] Ajouter ou adapter les tests fonctionnels et JavaScript nécessaires.

## Critères d'acceptation

1. L'onglet « Détails de la demande » présente les mêmes quatre zones et le
   même ordre métier que l'interface d'édition.
2. Chaque champ est consultable mais aucun champ de l'onglet ne permet de
   modifier la DAE ou de déclencher une mutation indirecte.
3. Les valeurs calculées ou formatées dans l'édition, notamment les dates, la
   rémunération, l'unité de rémunération et le nombre de jours ouvrés, sont
   présentées avec les mêmes règles métier.
4. Les champs réservés restent soumis aux droits de consultation de leur zone
   et ne sont pas exposés à un utilisateur non autorisé.
5. Les onglets Validation et Commentaires, les actions d'en-tête et les
   contrôles d'accès existants ne régressent pas.
6. La présentation reste utilisable sans chevauchement ni perte d'information
   sur les résolutions d'écran supportées.

## Vérifications

Exécuter les commandes adaptées via le Makefile racine :

- `make test.api` pour la vue de consultation et les autorisations ;
- `make test.unit` pour les règles de présentation ou de calcul ;
- `make test.style` si des tests sont ajoutés ;
- `make cs.check` et `make stan` si du PHP est modifié.

## Références

- `templates/Applicationforms/edit.php`
- `templates/Applicationforms/view.php`
- `templates/element/Applicationforms/zone_admin.php`
- `templates/element/Applicationforms/zone_contract.php`
- `templates/element/Applicationforms/zone_remuneration.php`
- `templates/element/Applicationforms/zone_reserves.php`
- `src/Policy/ApplicationformPolicy.php`
