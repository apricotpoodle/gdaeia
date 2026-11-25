# Cahier des Charges Fonctionnel
## Évolution de l'outil GDAE (V3)

Les réponses métier et le cadrage du plan approuvé figurent dans des colonnes
distinctes du [registre des questions ouvertes](questions-ouvertes.md). Les
règles encore ouvertes sont repérées par leur identifiant dans ce cahier.

---

## 1. Contexte et Objectifs

* **Objectif :** Refonte de l'outil de gestion des demandes d'embauche (GDAE) suite aux évolutions à la DSI.
* **Public cible.** Demandeurs (assistantes et chefs de service) et valideurs désignés par la configuration du service.
* **Besoin clé :** Fluidifier la saisie, automatiser les calculs de salaire, et permettre une administration autonome des référentiels (listes déroulantes).

---

## 2. Spécifications des Données (Champs de formulaire)

### 2.1 Informations Générales
* **Assistante :** Menu déroulant (Gestion dynamique) (Saisie assistée : auto-complétion). Un assistant est un user avec un rôle équivalent à demandeur. ajouter un champ assistant_id il doit êtrepar défaut le même que user_id, et s'il y a plusieurs user_id de role assistant et voyant le department de la dae, alors présenter une list déroulante avec saisie assistée.
* **Chef de service.** Afficher les noms des responsables issus des associations multiples du service de la DAE. Conserver la structure et les valeurs existantes de `applicationforms.manager_id` sans migration. L'application ignore ce champ, y compris pour les notifications (Q-0030-06).
* **Service :** Menu déroulant (Saisie assistée : auto-complétion). C'est un départment.
* **Code CGR :** Menu déroulant. Le code cgr existe déjà dans ApplicationformTable. La saisie dynamique actuelle en fonction de stratégie est parfaite ne pas modifier.

### 2.2 Informations du Poste
* **Champ :** C'est le champ jobtitle
* **Poste :** Menu déroulant. semble être une demande de liste basée sur les jobtitles déjà présents dans la base... (pas prioritaire une saisie libre suffira dans un premier temps)
* **Nom / Prénom :** Champs texte qui doivent créés dans la table ApplicationformTable puis saisis et ensuite être concaténés dans le champ applicantname
* **Catégorie pro / Statut / Temps de travail :** Menus déroulants
* **Date de début / Date de fin :** Date
  * *Règle métier :* Si CDI, blocage automatique du champ "Date de fin". Sinon, obligatoire et postérieure ou égale à la date de début.
* **Nombre de jours travaillés.** Afficher le champ numérique quand les dates de début et de fin sont valides et que la date de fin est activée. Seul `cdi` a une date de fin désactivée. `cdd`, `interim`, `appr.`, `stage`, `pige` et `fact` suivent la même règle (Q-0018-01).
  * Le nombre est facultatif. La valeur calculée est préremplie et peut être corrigée ou omise. Sans nombre saisi pour une rémunération en €/jour, le salaire de base reste vide (Q-0018-03).
  * Une période sans jour de semaine, comme un samedi et un dimanche, peut avoir zéro jour ouvré. Le zéro est refusé si la période contient un jour de semaine (Q-0018-02).
  * Aucun calcul particulier des jours fériés n'est appliqué : un jour férié tombant en semaine compte comme un jour de semaine (Q-0018-04).
  * Toute valeur de zéro déclenche un avertissement avant l'enregistrement, puis un courriel après la réussite de l'enregistrement à l'auteur, aux responsables associés au service et aux utilisateurs de rôle ADMIN qui voient ce service (Q-0018-05).
  * Quand les dates changent, présenter la valeur actuelle et le nouveau calcul pour laisser l'opérateur décider de la suite (Q-0018-06).
* **Motif RH :** Menu déroulant
* **Descriptif :** Champ libre (Zone de texte) pour le motif RH
* **Rémunération envisagée.** Les nouvelles saisies proposent €/jour, k/mois et €/mois. Les valeurs historiques en €/heure sont conservées; leur modification doit passer par une unité prise en charge (cadrage approuvé pour Q-0030-01).

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

* **Formatage :** Le système doit accepter le point (`.`) ou la virgule (`,`) indifféremment pour les formats décimaux.
* **Paramétrage.** Le coefficient global de k/mois (17,46) doit être modifiable par un super-administrateur, conformément au plan approuvé.
* **Nombre de jours omis.** Pour une rémunération en €/jour sans nombre de jours saisi, le salaire de base reste vide.
---

## 4. Administration et Paramétrage

* **Administration locale.** Les utilisateurs de rôle ADMIN peuvent gérer les affectations d'utilisateurs et de codes CGR existants aux services qu'ils voient. Les comptes, les rôles, les services et le catalogue CGR global sont hors du périmètre de cette administration locale.
* **Reprise d'une DAE.** Le plan retient la remise à zéro et la relance existantes pour les administrateurs habilités. La remise à zéro est réservée aux cycles qui n'ont pas atteint leur dernière séquence configurée. La confirmation métier sur les effets de la remise à zéro reste attendue (Q-0030-02). Voir l'[ADR 0076 proposé](../adr/0076-limiter-remise-a-zero-avant-derniere-sequence.md).
* **Gestion multi-chefs :** Possibilité d'associer plusieurs chefs de service à un même department.

---

## 5. Fonctionnalités de Recherche et Historique

* **Archivage.** Tous les types de DAE peuvent être archivés et restaurés, individuellement ou en masse, par un superuser ou un utilisateur de rôle ADMIN qui voit le service. La liste présente par défaut les DAE non archivées et permet de rechercher parmi toutes les DAE selon les droits habituels de lecture (Q-0030-03).
* **Cadrage de l'archivage.** Le plan retient une opération manuelle et réversible. Les cycles actifs sont exclus et les données ne font l'objet d'aucune purge automatique. Le délai d'éligibilité éventuel et la durée de conservation restent à préciser avec le métier (Q-0030-03).
* **Moteur de recherche :**
  * **Critères :** Par Nom, par CGR ;
  * **Filtre temporel :** Recherche sur période définie (ex : du JJ/MM/AAAA au JJ/MM/AAAA).

---

## 6. Flux de Travail (Workflow de validation)

### Séquences de validation

L'opérateur configure les séquences et les rôles par service avec les
mécanismes existants. L'ordre des rôles n'est pas codé en dur.

### Notifications (Q-0030-04)

* **Changements d'état.** Chaque changement d'état du cycle déclenche une notification en temps réel aux rôles concernés. Pour une DAE bloquée, les rôles pouvant outrepasser le blocage sont aussi notifiés.
* **Rappel quotidien à 07 h 00.** Les DAE en attente de validation sont classées par date. La liste est envoyée aux rôles concernés par l'étape.
* **Acceptation finale.** Une DAE acceptée (« signée ») déclenche une notification à l'assistante qui l'a saisie, à tous les responsables du service et à tout rôle ADMIN qui voit ce service.
* **Synthèse hebdomadaire.** La liste des DAE entièrement validées est envoyée à tout rôle ADMIN qui voit les services concernés.
* **Exécution des envois récurrents.** L'application fournit une commande CLI et une commande Make. L'exploitation planifie dans Airflow les traitements CLI au moyen de DAG et fixe le jour et l'heure de la synthèse hebdomadaire.

### Interface :
* Pour les rôles et users concernés,prévoir un bouton copié sur le modèle des cycles bloqués indiquant le nombre de dae à valider et permettant d'afficher uniquement les dae nécessitant une validation de leur part.
* Bouton de validation/envoi au niveau suivant de validation (signalétique : **Vert**)
* **Fonctionnalité Bonus :** Lors d'une duplication d'AE, suppression automatique des champs "Réservé au SG et suivant". Pour ce bouton l'effacement automatique des champs réservés au SG et roles suivcants, peut-il être paramétré en listant les champs concernés dans la table workflow_settings ?

---

## 7. Demandes complémentaires

* Rendre la validation du Contrôle de gestion (anciennement « Référencement ») lisible sur deux lignes dans le PDF. La liste exacte des autres champs d'impression reste à confirmer (Q-0030-07).
* Pour les services concernés de la rédaction de la SEM, le circuit « Facture » s'arrête au SGR et le circuit « Pige » au Contrôle de gestion, selon les règles locales décrites dans l'[ADR 0070](../adr/0070-circuits-courts-locaux-par-contrat.md).
* Voir l'ensemble des champs autorisés d'une DAE sans défilement. Tester sur un écran de référence de 1680 × 1050, dans une fenêtre non maximisée avec une zone de page de 1440 × 900, puis de 1366 × 768, à 100 % de zoom pour les deux tailles (Q-0030-05). Le plan prévoit Chrome, Edge et Firefox récents. Consigner les versions utilisées lors de la recette.

## 8. Recette transversale

Utiliser Docker local isolé et des données synthétiques. Vérifier les
parcours avec les séquences configurées par service par l'opérateur et lancer
les traitements de notification à l'aide des commandes locales. La recette
ne configure ni Airflow ni la production. Voir le
[ticket 0039](../backlog/0039-configurer-et-recetter-gdae-v3.md).
