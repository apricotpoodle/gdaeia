# ADR 0063 : Outrepassement des validations bloquantes du workflow

**Date :** 05 octobre 2026  
**Statut :** Proposé  
**Dépendances :** [ADR 0055](./0055-workflow-validation-applicationforms.md), [ADR 0056](./0056-remise-a-zero-cycle-validation.md), [ADR 0057](./0057-point-entree-fiche-reinitialisation-cycle.md)

## Contexte

L’ADR 0055 attribue chaque étape du cycle à un rôle et prévoit une
suppléance limitée après l’échéance. Une étape indisponible peut toutefois
bloquer le traitement alors qu’un opérateur habilité doit pouvoir poursuivre
le cycle. La règle d’autorisation doit rester identique dans la Policy, le
service, l’API et l’interface.

Le schéma contient déjà `validations.is_proxy` pour distinguer un vote réalisé
par un opérateur suppléant et `validations.obs` pour conserver le commentaire
final. Une nouvelle source de vérité ou une nouvelle table n’est donc pas
nécessaire.

## Décision

1. Un Admin (`User::ROLE_ADMIN`) ou un super-administrateur peut outrepasser
   une étape du cycle dont l’état est `en_attente` ou `a_venir`, sous réserve
   de pouvoir consulter la demande. Le droit est vérifié côté serveur par le
   service du workflow ; l’interface ne fait que présenter le résultat.
2. L’opérateur habilité dispose des mêmes décisions que le rôle de l’étape :
   accepter ou refuser. Le rôle porté par l’étape reste inchangé.
3. L’outrepassement est enregistré comme un vote ordinaire dans
   `validations`, avec `role_id` égal au rôle prévu, `user_id` égal à
   l’opérateur effectif et `is_proxy = true`. Le commentaire saisi est
   conservé ; s’il est vide, le serveur génère :
   `outrepassé par {adresse courriel} à {date et heure}`.
4. Une étape `a_venir` peut être traitée seule. Son traitement ne valide pas
   implicitement les étapes précédentes, n’active pas une chaîne d’étapes et
   ne modifie pas les autres votes. La progression normale reprend lorsque
   les étapes précédentes sont terminées. Un refus conserve son effet
   terminal prévu par l’ADR 0055.
5. Le service `ApplicationformValidationWorkflow` est l’unique source de la
   décision d’éligibilité et de la génération du commentaire par défaut.
   Les contrôleurs et JavaScript ne reproduisent pas cette règle.

## Justification

La réutilisation de `validations` et de `is_proxy` respecte DRY et évite une
double représentation de l’historique. La décision centralisée dans le
service respecte la séparation des responsabilités et empêche un contournement
par appel direct de l’API. La possibilité de traiter une étape bloquée sans
réécrire le cycle conserve l’immutabilité des votes tout en apportant le
déblocage opérationnel nécessaire.

## Conséquences

### Positives

* Un Admin peut débloquer une demande sans attendre une échéance ou supprimer
  le cycle.
* Chaque décision reste attribuée à l’opérateur effectif et au rôle prévu.
* Le commentaire automatique fournit une piste d’audit minimale et homogène.
* Aucune migration ni nouvelle configuration n’est nécessaire.

### Négatives

* Une étape future peut apparaître acceptée avant une étape précédente ; son
  effet sur la progression reste volontairement différé.
* Les Admins disposent d’un pouvoir métier sensible qui doit être couvert par
  les tests d’autorisation et l’audit des votes.

## Vérifications attendues

* Tester les droits Admin, super-administrateur et opérateur ordinaire sur les
  étapes actives et à venir.
* Tester les décisions accepter/refuser, `is_proxy`, le rôle conservé et le
  commentaire automatique.
* Tester qu’un outrepassement d’une étape à venir n’active pas implicitement
  les étapes suivantes.
