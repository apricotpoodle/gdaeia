# 0037 — Compléter les données imprimées du PDF

**Statut :** À planifier

**Priorité :** Moyenne — lisibilité des décisions

**Dépendance :** [0036 — Ajouter le compteur des demandes à valider](0036-ajouter-compteur-demandes-a-valider.md).

## Objectif

Rendre la validation du Contrôle de gestion lisible sur deux lignes. La
liste exacte des autres champs à ajouter au PDF reste à obtenir auprès du
métier (Q-0030-07). Les champs additionnels attendent cette réponse.

## Travail attendu

- [ ] Afficher la validation du Contrôle de gestion avec au moins deux lignes
  visibles, sans tronquer le contenu pertinent.
- [ ] Ajouter les autres champs d'impression une fois la réponse à
  Q-0030-07 consignée, avec leurs règles de formatage.
- [ ] Respecter les autorisations de champs et de zones déjà appliquées à la
  consultation Web.
- [ ] Vérifier les sauts de page et la lisibilité du PDF généré.

## Critères d'acceptation

1. La validation du Contrôle de gestion est lisible sur au moins deux lignes
   dans le PDF.
2. Les champs confirmés dans la réponse à Q-0030-07 sont imprimés avec les
   mêmes droits que leur consultation.
3. Le PDF ne révèle aucun champ réservé à un rôle non autorisé.
4. Une demande sans données optionnelles reste imprimable sans erreur.

## Vérifications

- `make test.unit` pour le formatage métier concerné
- `make test.api` pour la génération PDF et les autorisations
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0062 — Export PDF Applicationform](../adr/0062-export-pdf-applicationform.md)
- [ADR 0042 — ACL granulaire au niveau du champ](../adr/0042-acl-granulaire-niveau-champ-formulaire.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
