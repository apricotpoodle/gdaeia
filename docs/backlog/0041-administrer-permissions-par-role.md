# 0041 — Administrer les permissions par rôle

**Statut :** Prêt

**Priorité :** Haute

**Dépendance :** Socle générique des tickets 0007 et 0021 livré.

## Contexte

Le catalogue des permissions, les périmètres admissibles et les attributions
aux rôles sont définis par l'ADR 0069. Le vertical des départements permet déjà
d'auditer les permissions et leurs associations, mais l'administration
générique des attributions par rôle doit exposer le catalogue complet, y
compris les permissions qui ne sont attribuées à aucun rôle.

Dans la matrice de référence de l'ADR 0069, le rôle d'identifiant 2 est décrit
comme « Demandeur ». L'interface doit toutefois lire l'identifiant et le
libellé des rôles depuis la base, sans coder cette valeur en dur.

La grille présente les droits attribués au rôle choisi. Elle ne calcule ni ne
simule les droits effectifs d'une personne sur un objet, lesquels dépendent
aussi de son identité, de son périmètre et des règles métier.

## Objectif

Permettre au super-administrateur de consulter et de gérer les attributions de
permissions et de périmètres admissibles d'un rôle, avec un contrôle serveur et
une représentation fidèle du catalogue existant.

Le catalogue et la matrice initiale restent inchangés : ce ticket ne modifie
pas les attributions créées par les migrations et ne prévoit aucune attribution
initiale au rôle 2. Aucun changement de schéma n'est prévu.

## Travail attendu

Les cinq étapes sont strictement ordonnées. Une étape doit être terminée et
vérifiée avant de commencer la suivante.

1. [ ] Vérifier le contrat existant et inventorier les rôles, permissions,
   périmètres admissibles, attributions et URLs de menu associées, sans
   modifier les attributions initiales.
2. [ ] Étendre l'API de lecture pour exposer, selon le rôle choisi, le
   catalogue complet, les périmètres admissibles, les attributions du rôle et
   les URLs associées.
3. [ ] Afficher le sélecteur de rôle et une grille Tabulator distante qui
   présente aussi les permissions sans attribution.
4. [ ] Permettre l'ajout et le retrait des seules attributions admissibles,
   avec autorisation et validation côté serveur, et des erreurs conformes au
   contrat API.
5. [ ] Compléter la documentation, les tests et les vérifications adaptées au
   périmètre réellement modifié.

## Critères d'acceptation

1. L'écran Web et les endpoints concernés sont réservés au super-administrateur
   par les Policies ; un accès direct ou une requête forgée est également
   refusé.
2. Le sélecteur lit les identifiants et libellés des rôles depuis la base.
3. Pour le rôle sélectionné, la grille expose toutes les permissions, y
   compris celles qui n'ont aucune attribution, leurs périmètres admissibles,
   les attributions du rôle et les URLs de menu associées lorsqu'elles
   existent.
4. La grille décrit les attributions du rôle et ne présente pas les droits
   effectifs d'une personne sur un objet.
5. Une attribution ne peut être créée qu'avec une permission du catalogue et
   un périmètre admissible pour cette permission. Les périmètres invalides sont
   refusés côté serveur avec une erreur API de validation appropriée.
6. Le super-administrateur peut ajouter et retirer une attribution existante
   par les mutations prévues ; les autres identités ne peuvent pas les
   effectuer.
7. La consultation et la gestion n'ajoutent, ne retirent et ne remplacent
   aucune attribution initiale ; aucune attribution initiale au rôle 2 n'est
   créée et aucun changement de schéma n'est introduit.
8. L'API conserve le contrat d'erreur `{success, message, errors}` et utilise
   HTTP 422 pour les erreurs de validation pertinentes.

## Vérifications

- Ajouter des tests couvrant les accès Web et API autorisés/refusés, la lecture
  du catalogue incluant les permissions sans attribution, les mutations
  autorisées et les périmètres invalides.
- Exécuter `make test.unit` et `make test.api`.
- Exécuter `make test.integration` si les requêtes ORM changent.
- Exécuter `make test.style` si les tests sont modifiés.
- Exécuter `make cs.check` et `make stan` si du PHP est modifié.
- Vérifier que les attributions initiales et le schéma restent inchangés.

## Références

- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0065 — ACL automatique des mutations Web et API](../adr/0065-acl-automatique-des-mutations-web-api.md)
- [ADR 0004 — API Tabulator et patrons de conception](../adr/0004-api-tabulator-et-design-patterns.md)
- [Ticket 0007 — Séparer les accès aux menus des permissions d'administration](0007-separer-acces-menus-et-permissions-administration.md)
- [Ticket 0021 — Implémenter les habilitations génériques](0021-implementer-les-habilitations-generiques.md)
