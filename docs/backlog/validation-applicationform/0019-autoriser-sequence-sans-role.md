# 0019 — Autoriser une séquence sans rôle

**Statut :** Terminé  
**Dépendance :** 0012

## Contexte

Sur `/validationsequences`, retirer le dernier rôle associé à la sélection
échoue avec le message « Chaque département doit disposer d’une séquence
continue, commençant à 1. ». Une configuration sans rôle validateur doit
pourtant être autorisée afin de pouvoir repartir d'une liste vide.

## Correctif attendu

Adapter la vérification de continuité des séquences pour qu'elle accepte un
département sans aucun rôle actif. La continuité doit rester imposée dès qu'au
moins un rôle est configuré : les numéros doivent alors commencer à 1 et ne
présenter aucun trou.

## Critères d'acceptation

1. Un Super Admin peut supprimer le dernier rôle de la sélection depuis
   `/validationsequences`.
2. La mutation API réussit et aucun rôle actif ne subsiste pour les
   départements concernés.
3. Une configuration non vide présentant un trou de séquence reste refusée.
4. Les tests HTTP et ORM couvrent ces deux comportements.

## Vérifications

- make test.api
- make test.integration
- make cs.check
- make stan
