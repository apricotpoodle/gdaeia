# 0038 — Respecter l'affichage sans défilement

**Statut :** À planifier

**Priorité :** Moyenne — ergonomie de saisie

**Dépendance :** [0037 — Compléter les données imprimées du PDF](0037-completer-donnees-imprimees-pdf.md).

## Objectif

Adapter la vue de saisie aux dimensions d'écran et au parcours convenus au
0030, en précisant ce que signifie l'exigence « sans ascenseur ».

La réponse à Q-0030-05 fixe un écran de référence de 1680 × 1050 et une
fenêtre non maximisée avec une zone de page de 1440 × 900. Un contrôle
complémentaire utilise une zone de page de 1366 × 768. Le zoom est de 100 %
aux deux tailles. Le plan approuvé retient Chrome, Edge et Firefox récents.

## Travail attendu

- [ ] Effectuer la recette aux deux tailles de page dans Chrome, Edge et
  Firefox récents et consigner leurs versions.
- [ ] Ajuster la disposition des zones et commentaires pour rendre le
  parcours utilisable dans ces conditions, sans masquer de champ ni d'action.
- [ ] Conserver les règles d'autorisation, les messages de validation et
  l'accessibilité clavier.
- [ ] Si la solution amende l'ADR 0048 accepté, créer un nouvel ADR au statut
  `Proposé` et mettre à jour son index ; ne pas réécrire l'ADR existant.
- [ ] Ajouter une vérification visuelle reproductible aux dimensions
  convenues et documenter ses limites.

## Critères d'acceptation

1. Le parcours répond à la définition et aux dimensions confirmées au 0030.
2. Aucun champ, avertissement, action ou erreur de validation ne devient
   inaccessible.
3. Les vues réduites restent utilisables au clavier et ne se chevauchent pas.
4. Toute évolution architecturale de l'ADR 0048 est proposée dans un nouvel
   ADR et non appliquée rétroactivement.

## Vérifications

- `make test.unit`
- `make test.api` pour le rendu Web et les droits
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié
- Recette visuelle aux résolutions et navigateurs convenus

## Références

- [ADR 0048 — Découpage de l'IHM Applicationform](../adr/0048-decoupage-ihm-zones-applicationform.md)
- [ADR 0013 — Architecture frontend JavaScript](../adr/0013-architecture-frontend-js.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
