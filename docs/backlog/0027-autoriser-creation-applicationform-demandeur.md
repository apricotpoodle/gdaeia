# 0027 — Autoriser la création d'une Applicationform par un demandeur

**Statut :** Terminé — implémenté sur `feature/tickets-0028-0027`

**Priorité :** Haute — création et habilitations des demandes

**Dépendance :** Le ticket [0028 — Améliorer l'IHM des autorisations de
champs](0028-ameliorer-ihm-autorisations-champs.md) doit être terminé avant
celui-ci. Il fournit le modèle stabilisé pour distinguer les champs ORM des
zones fonctionnelles et configurer les droits du demandeur.

## Contexte

L'URL `/applicationforms/add` utilise une entité `Applicationform` vide. Les
Policies actuelles appliquent principalement les règles de visibilité et de
modification d'une demande déjà enregistrée. Une nouvelle demande ne possède
encore ni `user_id`, ni `department_id`, ce qui peut empêcher le rôle
`Demandeur` de modifier la zone administrative et de renseigner les champs
nécessaires à l'enregistrement.

Les autorisations de champs configurées dans `field_authorizations` ne
suffisent pas à elles seules : elles sont évaluées après l'autorisation de la
zone par la Policy. Les listes de référence et le périmètre des départements
doivent également rester filtrés côté serveur.

## Objectif

Permettre à un utilisateur ayant le rôle `Demandeur` de saisir et enregistrer
une nouvelle demande depuis `/applicationforms/add`, sans élargir ses droits
sur les demandes déjà existantes ni exposer les champs réservés à
l'administration ou aux RH.

## Travail attendu

- [ ] Distinguer explicitement, dans `ApplicationformPolicy`, une entité
  nouvelle d'une demande persistée pour l'autorisation des zones de création.
- [ ] Autoriser le demandeur à consulter et modifier les zones métier
  nécessaires à la création : administration/candidat, contrat et
  rémunération/temps de travail.
- [ ] Maintenir la protection de la zone réservée RH/administration et des
  demandes déjà enregistrées hors périmètre.
- [ ] Vérifier la cohérence entre les règles `field_authorizations` du rôle
  `Demandeur` et les champs effectivement nécessaires au formulaire.
- [ ] Vérifier que les listes de référence restent limitées aux valeurs
  actives et que les départements/collaborateurs respectent le périmètre de
  l'utilisateur.
- [ ] Conserver le filtrage serveur de `authorizedRequestData()` et
  l'attribution automatique du `user_id` au demandeur connecté.
- [ ] Ajouter les tests de Policy, de formulaire Web et d'enregistrement
  réussi ou refusé.
- [ ] Mettre à jour la documentation locale si le contrat d'habilitation des
  zones évolue.

## Critères d'acceptation

1. Un demandeur authentifié peut ouvrir `/applicationforms/add` sans erreur.
2. Les listes déroulantes métier sont visibles et contiennent uniquement les
   options autorisées par le périmètre courant.
3. Le demandeur peut renseigner les champs métier requis, notamment
   `jobtitle`, puis enregistrer la demande.
4. La demande créée reçoit automatiquement l'identifiant du demandeur
   connecté comme `user_id`.
5. Les champs réservés RH/administration restent non modifiables et ne
   peuvent pas être injectés par une requête fabriquée.
6. Les règles de modification, de consultation et de suppression des demandes
   existantes ne régressent pas.
7. Les erreurs de validation sont présentées en français et empêchent la
   création tant que les champs requis sont invalides.

## Vérifications

Exécuter les commandes adaptées via le Makefile racine :

- `make test.api` pour la route Web et les autorisations ;
- `make test.unit` pour les règles de Policy et les services concernés ;
- `make test.style` si des tests sont ajoutés ;
- `make cs.check` et `make stan` si du PHP est modifié.

## Références

La Policy distingue désormais l’entité nouvelle d’une demande persistée. Un
demandeur peut saisir les zones métier nécessaires à la création ; les champs
RH et administration réservés sont retirés côté serveur avant l’hydratation
ORM, et l’identifiant du demandeur reste attribué par le contrôleur.

- `src/Policy/ApplicationformPolicy.php`
- `src/Controller/ApplicationformsController.php`
- `templates/Applicationforms/add.php`
- `templates/element/Applicationforms/zone_admin.php`
- `src/Service/Security/FieldAuthorizationService.php`
- [ADR 0042 — ACL granulaire au niveau du champ](../adr/0042-acl-granulaire-niveau-champ-formulaire.md)
- [ADR 0048 — Découpage de l'IHM Applicationform en zones](../adr/0048-decoupage-ihm-applicationform.md)
- [ADR 0065 — ACL automatique des mutations Web et API](../adr/0065-acl-automatique-des-mutations-web-api.md)
