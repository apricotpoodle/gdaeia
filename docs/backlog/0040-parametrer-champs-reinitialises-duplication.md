# 0040 — Paramétrer les champs réinitialisés à la duplication

**Statut :** Facultatif — à examiner après le ticket 0039

**Priorité :** Basse — bonus

**Dépendance :** Séquence 0030–0039 terminée et bonus expressément retenu.
Ce ticket n'appartient pas au manifeste obligatoire.

## Objectif

Réintroduire, si le bonus est retenu après la séquence obligatoire, un
paramétrage contrôlé de la liste de champs réinitialisés lors de la duplication
d'une DAE.

## Travail attendu

- [ ] Confirmer le besoin et les rôles habilités à modifier ce paramétrage.
- [ ] Proposer un nouvel ADR applicatif qui révise le choix de la liste
  canonique codée dans `ApplicationformsTable::DUPLICATION_RESET_FIELDS`, tel
  que livré au ticket 0020. L'ADR 0046 porte sur les autorisations de champs
  et ne traite pas de la duplication.
- [ ] Permettre de configurer une liste validée de champs sans exposer les
  champs d'audit, d'identité ou de workflow protégés.
- [ ] Conserver un défaut sûr et documenté lorsque le paramétrage est absent
  ou invalide.
- [ ] Tester les champs autorisés, interdits, inconnus et les droits de
  modification.

## Critères d'acceptation

1. Le bonus est retenu explicitement après le 0039 et son ADR proposé est
   revu avant l'implémentation.
2. Le paramétrage ne permet pas de recopier l'identité, les votes ou les
   données d'audit interdits.
3. Les règles de duplication actuelles restent cohérentes avec un paramétrage
   absent ou invalide.

## Vérifications

- `make test.unit`
- `make test.integration` si le stockage de configuration change
- `make test.api` pour l'administration et la duplication
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [0020 — Fiabiliser la duplication des DAE](0020-fiabiliser-la-duplication-des-dae.md)
