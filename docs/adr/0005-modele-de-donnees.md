# ADR 0005 : Architecture de la Base de Données

**Date :** 02 Juillet 2026
**Statut :** Accepté
**Moteur :** MySQL (InnoDB, utf8mb4)

## Contexte et Modèle Conceptuel
La base de données soutient une application de gestion de formulaires (DAE) intégrant un moteur de workflow (validation séquentielle) et une ségrégation des données par département et par rôle.

L'architecture est divisée en 5 grands domaines fonctionnels :

### 1. Cœur Métier (Les Demandes)
C'est le centre de gravité de l'application.
* `applicationforms` : Table principale stockant les demandes d'emploi/recrutement.
* `validations` & `applicationvalidationsteps` : Stockage des visas posés sur une demande.

### 2. Moteur de Workflow (Configuration)
Définit les règles de validation avant même qu'une demande ne soit créée.
* `validationsequences` : Définit l'ordre de validation (séquence) requis pour chaque département et rôle.
* `validationstatuses` : Dictionnaire des états (Brouillon, En attente, Validé, Rejeté...).

### 3. Gestion des Accès et de l'Organisation (ACL)
* `users`, `roles`, `departments` : La trinité classique de la gestion d'organisation.
* `user_departments` & `role_menus` : Tables de liaison (Many-to-Many) pour délimiter les périmètres de visibilité.
* `field_authorizations` : (Très spécifique) Permet une granularité de sécurité au niveau du champ de formulaire selon le rôle.

### 4. Référentiels et Nomenclatures
Tables satellites de configuration (souvent préfixées ou utilisées pour les listes déroulantes).
* Exemples : `contracttypes`, `hiringreasons`, `professionalcategories`, `budgetfeatures`, `cgr_codes`, `cgr_strategies`.

### 5. Vues SQL (Optimisation)
Pour éviter de surcharger l'ORM CakePHP avec des jointures complexes lors des calculs d'état, la logique est déportée dans MySQL via des vues :
* `applicationformstatuses` : Calcule en temps réel si une demande est en cours, acceptée ou rejetée.
* `currentvalidationroles` : Identifie à qui le tour de valider.
* `validation_visas` : Aplatit l'historique des validations pour l'affichage (chronologie).