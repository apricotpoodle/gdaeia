# 0008 — Adapter l'interface aux utilisateurs non gérables

**Statut :** À planifier

**Dépendances :** 0005, 0006 et 0007

## Objectif

Rendre visible la différence entre consultation et mutation sans faire de
l'interface une frontière de sécurité.

## Travail attendu

- Marquer les lignes non gérables en lecture seule.
- Désactiver les actions de modification, association et dissociation.
- Afficher les messages métier en français.
- Conserver l'affichage des associations existantes nécessaires à la lecture.
- Ne pas ajouter de logique d'autorisation uniquement en JavaScript.

## Vérifications

- Tests JavaScript et tests fonctionnels des grilles.
- Vérification que les actions masquées restent refusées côté serveur.

