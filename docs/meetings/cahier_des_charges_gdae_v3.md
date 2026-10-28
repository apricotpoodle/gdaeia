# Cahier des Charges Fonctionnel
## Évolution de l'outil GDAE (V3)

---

## 1. Contexte et Objectifs

* **Objectif :** Refonte de l'outil de gestion des demandes d'embauche (GDAE) suite aux évolutions à la DSI.
* **Public cible :** Demandeur-Assistante / Chef de service, Valideur 1-Secrétariat Général, Valideur 2-Contrôle de Gestion, Valideur 3-DRH, Valideur final-Directoire.
* **Besoin clé :** Fluidifier la saisie, automatiser les calculs de salaire, et permettre une administration autonome des référentiels (listes déroulantes).

---

## 2. Spécifications des Données (Champs de formulaire)

### 2.1 Informations Générales
* **Assistante :** Menu déroulant (Gestion dynamique) (Saisie assistée : auto-complétion). Un assistant est un user avec un rôle équivalent à demandeur. ajouter un champ assistant_id il doit êtrepar défaut le même que user_id, et s'il y a plusieurs user_id de role assistant et voyant le department de la dae, alors présenter une list déroulante avec saisie assistée.
* **Chef de service :** pas de saisie par l'opérateur donc pas de champ manager_id dans l'enité applicationform. Le ou les chefs de service sont associés à un arbre de departments par un daministrateur sur un autre écran (autre url). Un chef de service est un user avec un rôle chef de service équivalent à demandeur. il suffit d'affichier le ou les noms des chefs de serive associés à la dae courante.
* **Service :** Menu déroulant (Saisie assistée : auto-complétion). C'est un départment.
* **Code CGR :** Menu déroulant. Le code cgr existe déjà dans ApplicationformTable. La saisie dynamique actuelle en fonction de stratégie est parfaite ne pas modifier.

### 2.2 Informations du Poste
* **Champ :** C'est le champ jobtitle
* **Poste :** Menu déroulant. semble être une demande de liste basée sur les jobtitles déjà présents dans la base... (pas prioritaire une saisie libre suffira dans un premier temps)
* **Nom / Prénom :** Champs texte qui doivent créés dans la table ApplicationformTable puis saisis et ensuite être concaténés dans le champ applicantname
* **Catégorie pro / Statut / Temps de travail :** Menus déroulants
* **Date de début / Date de fin :** Date
  * *Règle métier :* Si CDI, blocage automatique du champ "Date de fin". Sinon, obligatoire et postérieure ou égale à la date de début.
* **Nombre de jours travaillés :** Champ numérique à rajouter dans applicationformTable
  * *Règle métier :* Uniquement pour les piges / factures, bloquant si non renseigné. Absent pour les CDI / CDD.
* **Motif RH :** Menu déroulant
* **Descriptif :** Champ libre (Zone de texte) pour le motif RH
* **Rémunération envisagée :** Menu déroulant (Unités : €/j, k/mois, €/mois, €/heure)

### 2.3 Section à remplir par le Secrétariat Général et les valideurs suivants
* **Salaire de base :** Format décimal (gestion du "." et ","). une valeur précalculée est préremplie.
* **Validation Contrôle de Gestion :** Champ libre (appelé *Référencement* dans la V1). Prévoir suffisament de place.
* **Motif budgétaire :** Menu déroulant
* **Argumentaire :** Champ libre, pour l'argument final pour la validation finale par la direction

---

## 3. Règles de Gestion et Calculs Automatiques

Le système doit appliquer les règles suivantes au calcul du "Salaire de base" :

| Condition (Rémunération envisagée) | Formule de calcul |
| :--- | :--- |
| **Unité : €/jour** | Jours travaillés * € |
| **Unité : k/mois** | Valeur (k) * 17,46 |
| **Unité : €/mois** | Valeur brute (sans calcul) |
| **Unité : €/heure** | *(Non spécifié dans le document)* |

* **Formatage :** Le système doit accepter le point (`.`) ou la virgule (`,`) indifféremment pour les formats décimaux.
* **Paramétrage :** La valeur du k (17,46) doit pouvoir être modifiée dans l'écran de paramétrage adéquat.
---

## 4. Administration et Paramétrage

* **Autonomie :** Créer un profil "Administrateur" pour le Secrétariat Général de la Rédaction :
  * le SGR est un department, donc certains users seront administrateurs de l'arbre SGR et tous ses sous departments.
  * Permettant la modification/ajout des valeurs dans les menus déroulants (Chef de service, Assistantes, Service, CGR) sans intervention technique tierce ;
  * Donnant la possibilité de récupérer une AE déjà passée au niveau de validation suivant (avant la dernière étape).
* **Gestion multi-chefs :** Possibilité d'associer plusieurs chefs de service à un même department.

---

## 5. Fonctionnalités de Recherche et Historique

* **Historique :** Archivage complet de toutes les demandes AE.
* **Moteur de recherche :**
  * **Critères :** Par Nom, par CGR ;
  * **Filtre temporel :** Recherche sur période définie (ex : du JJ/MM/AAAA au JJ/MM/AAAA).

---

## 6. Flux de Travail (Workflow de validation)

### Séquence de validation :
1. Demandeur
2. Secrétariat général / Directeur du Journal
3. Contrôle de gestion (+ visu RRH)
4. DRH
5. RRH contrat signé

### Notifications :
* **Quotidienne :**
  * AE en attente de validation classées par date (push mail aux valideurs) ;
  * AE signées (push mail au demandeur, chef de service et SGR).
* **Hebdomadaire :** Synthèse des AE entièrement validées.

### Interface :
* Pour les rôles et users concernés,prévoir un bouton copié sur le modèle des cycles bloqués indiquant le nombre de dae à valider et permettant d'afficher uniquement les dae nécessitant une validation de leur part.
* Bouton de validation/envoi au niveau suivant de validation (signalétique : **Vert**)
* **Fonctionnalité Bonus :** Lors d'une duplication d'AE, suppression automatique des champs "Réservé au SG et suivant". Pour ce bouton l'effacement automatique des champs réservés au SG et roles suivcants, peut-il être paramétré en listant les champs concernés dans la table workflow_settings ?

---

## 7. Demandes complémentaires

* Augmenter le nombre de caractères visibles à l'impression du champ "Référencement" (nouveau nom : validation Contrôle de Gestion) (2 lignes).
* Création d'un bouton "Archiver" sur les rôles SGR et Direction Financière pour factures et piges.
* Circuit "facture" s'arrête au niveau SGR / Circuit "pige" s'arrête au niveau Contrôle de Gestion.
* Voir l'ensemble des champs d'une dae sur une seule vue (pas d'ascenseur).
