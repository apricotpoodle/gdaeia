# ADR 0057 : Point d’entrée fiche pour la réinitialisation d’un cycle

**Date :** 18 septembre 2026
**Statut :** Accepté  
**Dépendance :** [ADR 0056](./0056-remise-a-zero-cycle-validation.md)

## Décision

La réinitialisation autorisée par l’ADR 0056 reste une unique mutation API
et peut être déclenchée depuis la grille ou la fiche d’une demande. La fiche
réutilise la même commande de domaine, la même Policy et la même confirmation
explicite.

La remise à zéro n’envoie pas de courriel et ne crée pas de commentaire d’audit
persistant : elle restitue strictement l’état sans cycle et les courriels déjà
transmis ne peuvent pas être retirés.
