# 0035 — Compléter les notifications GDAE

**Statut :** À planifier

**Priorité :** Haute — suivi des demandes

**Dépendance :** [0034 — Finaliser l'archivage et l'accès aux archives](0034-finaliser-archivage-et-acces-archives.md).

## Objectif

Achever les notifications de validation et d'acceptation ainsi que les rappels
quotidiens et la synthèse hebdomadaire, à partir des règles confirmées au 0030.
L'application fournit une commande CLI et une commande Make pour lancer les
traitements manuellement ; Airflow planifie les traitements CLI au moyen de
DAG.

## Travail attendu

- [ ] Envoyer en temps réel une notification aux rôles concernés à chaque
  changement d'état du cycle de validation ; inclure les rôles pouvant
  outrepasser le blocage lorsqu'une DAE est bloquée.
- [ ] À l'acceptation finale d'une DAE, notifier l'assistante qui l'a saisie,
  tous les responsables associés au service et tous les rôles ADMIN qui voient
  ce service.
- [ ] Fournir le traitement CLI du rappel quotidien des demandes en attente,
  adressé aux rôles concernés par l'étape ; le rappel est prévu à 07 h 00.
- [ ] Fournir le traitement CLI de la synthèse hebdomadaire des DAE entièrement
  validées, adressé à tous les rôles ADMIN qui voient les services concernés.
- [ ] Fournir une commande Make permettant de lancer manuellement les
  traitements CLI ; la planification des DAG Airflow est hors du périmètre de
  l'application.
- [ ] Gérer le fuseau horaire applicatif, les envois sans destinataire, les
  échecs et les relances sans doublons indus.
- [ ] Documenter les commandes, destinataires, heures et planification
  opératoire sans appliquer de configuration de production.

## Critères d'acceptation

1. Les changements d'état du cycle déclenchent les notifications en temps réel
   prévues au 0030, avec les destinataires filtrés selon les rôles et périmètres.
2. Les traitements CLI quotidiens et hebdomadaires appliquent les destinataires
   décidés au 0030 et peuvent être lancés avec une commande Make.
3. Airflow peut planifier les commandes sans configuration de planification
   propre à l'application ; l'heure du rappel quotidien est 07 h 00.
4. Une exécution répétée ne crée pas de notifications en double hors règle
   explicite, et les erreurs d'envoi sont traçables sans exposer de données
   sensibles.

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
