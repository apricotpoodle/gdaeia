# 0035 — Compléter les notifications GDAE

**Statut :** À planifier

**Priorité :** Haute — suivi des demandes

**Dépendance :** [0034 — Finaliser l'archivage et l'accès aux archives](0034-finaliser-archivage-et-acces-archives.md).

## Objectif

Achever les notifications quotidiennes de validation et de signature ainsi
que la synthèse hebdomadaire, à partir des destinataires et horaires confirmés
au 0030.

## Travail attendu

- [ ] Réutiliser les commandes existantes de synthèse quotidienne des DAE en
  attente et de synthèse hebdomadaire des DAE acceptées ; compléter leurs
  destinataires selon la décision du 0030.
- [ ] Vérifier le tri par date et la planification réelle des commandes dans
  l'environnement convenu ; documenter les heures retenues.
- [ ] Notifier la signature aux destinataires confirmés, dont le demandeur,
  les responsables de service et le SGR si retenus.
- [ ] Compléter la synthèse hebdomadaire des demandes entièrement validées et
  ajouter la notification quotidienne des demandes signées si retenue.
- [ ] Gérer le fuseau horaire applicatif, les envois sans destinataire, les
  échecs et les relances sans doublons indus.
- [ ] Documenter les commandes, destinataires, heures et planification
  opératoire sans appliquer de configuration de production.

## Critères d'acceptation

1. Les listes quotidiennes, notifications de signature et synthèses
   hebdomadaires correspondent aux destinataires et horaires actés au 0030.
2. Les données restent filtrées par les droits et périmètres applicables.
3. Une exécution répétée ne crée pas de notifications en double hors règle
   explicite.
4. Les erreurs d'envoi sont traçables sans exposer de données sensibles.

## Vérifications

- `make test.unit`
- `make test.api` si des endpoints ou contrôleurs changent
- `make test.workflow` si les transitions ou vues SQL changent
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0071 — Fuseau horaire applicatif Europe/Paris](../adr/0071-fuseau-horaire-applicatif-europe-paris.md)
- [ADR 0036 — Encapsulation de l'expédition des courriels](../adr/0036-encapsulation-expedition-email-safesend.md)
- `src/Command/`
- `src/Mailer/`
