# 0011 — Service et Policies

**Statut :** Terminé  
**Dépendance :** 0010

## Dérogation de qualité — 17 septembre 2026

Le périmètre de ce ticket est validé malgré l'échec des contrôles globaux :

* `make test.unit`, `make test.integration` et `make test.workflow` réussissent ;
* PHPCS et PHPStan réussissent sur le service, la Policy et les entités du workflow ;
* les échecs globaux PHPCS sont suivis par le ticket backlog 0009 ;
* les erreurs globales PHPStan sont suivies par le ticket backlog 0011.

Cette dérogation est limitée à ce ticket et ne modifie pas l'exigence générale
de contrôles globaux verts avant livraison.

## Prompt Codex

Crée le service transactionnel de lancement et de vote, avec les Policies de visibilité, lancement, vote, suppléance et édition active.
