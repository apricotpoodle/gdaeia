# ADR 0042 : ACL Granulaire au Niveau du Champ de Formulaire (Field-Level Security)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
La création et la modification d'entités (ex: `Users`) requièrent une granularité de sécurité supérieure à la simple validation de route. Certains profils d'opérateurs peuvent créer un utilisateur sans pour autant avoir le droit d'élever ses privilèges (modifier `issuperuser` ou altérer le `role_id`).

## Décision
1. **Couche Données (`field_authorizations`)** : Utilisation de la table dédiée pour mapper les couples `[role_id, resource, field]` vers un niveau d'accès (`EDIT`, `VIEW`, `NONE`).
2. **Double Validation (Backend + Frontend)** :
   * **Front-End (`create.js`)** : Interroge l'API pour récupérer son schéma et brider dynamiquement le DOM (masquage `d-none` ou passage en lecture seule `disabled`).
   * **Back-End (`FieldAuthorizationService`)** : Réceptionne le POST et purge immédiatement (`unset`) toute clé de données ne disposant pas du droit `EDIT` avant de la passer au `patchEntity`.

## Justification (Principes SOLID)
Cette approche respecte la **Défense en Profondeur**. Le bridage front-end offre une excellente expérience utilisateur, tandis que le filtrage strict back-end empêche toute tentative de falsification de requêtes (Mass-Assignment / Parameter Tampering) par un utilisateur malveillant manipulant la console réseau.
