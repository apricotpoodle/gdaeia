# Préparation de la rencontre — Application DAE

**Objet :** proposer un cadre fonctionnel à valider avec les personnes demandant la fabrication de l’application.

**Statut :** document de travail — les propositions marquées « À valider » ne constituent pas encore des décisions.

## 1. Objectifs de la rencontre

La rencontre doit permettre de confirmer :

- les rôles des personnes qui créent, instruisent, valident et administrent une DAE ;
- les informations visibles ou modifiables selon chaque rôle ;
- le cycle complet d’une DAE, y compris les refus, les délais et les relances ;
- la politique de conservation et d’archivage ;
- la stratégie de reprise des utilisateurs, rôles, services et anciennes demandes ;
- le comportement attendu pour créer une nouvelle DAE à partir d’une DAE existante.

Les choix techniques déjà présents dans l’application — autorisation serveur, droits au niveau des objets métier, workflow par séquences et séparation des commentaires — servent de base, mais ne remplacent pas la validation métier.

## 2. Rôles proposés

Les intitulés suivants correspondent aux rôles actuellement représentés dans l’application. Ils doivent être confirmés avec les utilisateurs :

| Rôle proposé | Responsabilité principale |
|---|---|
| Administrateur | Administrer les utilisateurs, les référentiels, les droits et, si nécessaire, intervenir sur une DAE visible. |
| Demandeur | Créer une DAE, la compléter et la lancer dans le circuit. |
| Valideur de pôle | Examiner la cohérence opérationnelle de la demande. |
| Valideur RRH | Examiner les aspects ressources humaines. |
| Valideur DRH | Effectuer la validation RH de niveau supérieur. |
| Valideur contrôle de gestion | Vérifier le financement et les éléments budgétaires. |
| Valideur direction | Rendre la décision finale lorsque le circuit le prévoit. |

Un même utilisateur peut être rattaché à plusieurs services. Son rôle principal et son périmètre de services doivent être distingués : le rôle détermine ce qu’il peut faire, le service détermine quelles données il peut voir.

## 3. Proposition de droits

La proposition détaillée figure dans [l’annexe de réunion](dae-annexe-droits-migration-cycle.md).

Le principe recommandé est le suivant :

- le demandeur peut modifier sa DAE tant qu’elle n’est pas lancée ;
- les valideurs consultent les données nécessaires à leur décision et ne modifient pas les données du demandeur, sauf règle explicitement validée ;
- l’administrateur peut intervenir selon sa visibilité sur la DAE, mais toute intervention est tracée ;
- les informations d’audit, les votes et les étapes du workflow ne sont jamais modifiables comme de simples champs de formulaire ;
- le contrôle serveur est obligatoire, même lorsqu’un champ est masqué ou désactivé dans l’interface.

## 4. Cycle proposé d’une DAE

```text
Brouillon
   │
   ├─ modification par le demandeur
   │
   ▼
Lancement du circuit
   │  copie de la configuration applicable au service
   ▼
Validation séquence par séquence
   │
   ├─ refus motivé ───────────────► Refusée
   │
   ├─ délai dépassé ──────────────► Relance / suppléance autorisée
   │
   └─ toutes les séquences acceptées ► Acceptée
   │
   ▼
Conservation puis archivage logique
```

### Règles proposées

1. Le demandeur crée et enregistre la DAE comme brouillon.
2. Le demandeur lance le circuit ; un administrateur peut le faire à sa place s’il voit la DAE.
3. Au lancement, les étapes et rôles du service sont copiés dans l’exécution du circuit. Une modification ultérieure du paramétrage ne change pas le circuit déjà lancé.
4. Chaque rôle de la séquence doit accepter avant l’ouverture de la séquence suivante.
5. Un refus motivé clôt le circuit.
6. Les relances sont envoyées lorsque le délai configuré est dépassé.
7. Un administrateur habilité peut suppléer un rôle après expiration de son délai.
8. Les commentaires généraux de la DAE restent séparés des commentaires de décision.

## 5. Visibilité et archivage — décision attendue

Deux options sont possibles :

| Option | Effet | Avis proposé |
|---|---|---|
| Conservation visible en permanence | Toutes les DAE restent dans la liste, avec filtres par état. | Simple, mais la liste peut devenir difficile à exploiter. |
| Archivage logique configurable | Après une durée validée, la DAE reste consultable en lecture seule dans les archives. | **Recommandée** : l’historique est conservé sans encombrer les listes courantes. |

Questions à trancher :

- après combien de temps une DAE acceptée, refusée ou abandonnée est-elle archivée ?
- les DAE refusées doivent-elles suivre la même durée de conservation ?
- qui peut consulter les archives ?
- une DAE archivée peut-elle être désarchivée, ou seulement dupliquée ?
- existe-t-il une durée légale ou métier de conservation ?

La suppression physique est déconseillée : elle ferait perdre l’historique et rendrait les contrôles ultérieurs plus difficiles.

## 6. Reprise des anciennes données

Le dépôt ne contient pas le schéma de l’ancienne application. La migration doit donc commencer par une phase d’inventaire et de rapprochement, et non par un mapping définitif supposé.

La démarche proposée est :

1. obtenir un export ou un accès en lecture à l’ancienne base ;
2. inventorier les utilisateurs, rôles, services, rattachements, DAE et historiques de validation ;
3. dédoublonner les utilisateurs par courriel, identifiant historique puis nom/prénom ;
4. rapprocher les anciens rôles des rôles cibles ;
5. reprendre l’ensemble des rattachements d’un utilisateur à ses services ;
6. produire un rapport des correspondances incertaines ou impossibles ;
7. importer d’abord dans un environnement de test ;
8. contrôler les volumes, les doublons, les utilisateurs sans rôle et les services disparus ;
9. valider le résultat avant la reprise en production.

Les mots de passe, comptes inactifs et données personnelles particulières feront l’objet d’une décision spécifique. Les secrets ne doivent pas être exportés dans les fichiers de migration ou de documentation.

## 7. Duplication d’une DAE existante

La fonction proposée est : **« Créer une nouvelle DAE à partir de cette DAE »**.

Elle crée une nouvelle demande indépendante, dans l’état brouillon, appartenant à l’utilisateur courant. Elle ne relance jamais automatiquement l’ancien circuit et ne copie aucun vote.

À copier, sous réserve que les références soient encore actives :

- service concerné ;
- type de contrat ;
- motif de recrutement ;
- motif de remplacement ;
- financement ;
- intitulé du poste ;
- catégorie professionnelle ;
- temps de travail et répartition ;
- rémunération et périodicité ;
- qualification ;
- indicateurs métier ;
- candidat ou collaborateur, uniquement si cette information reste pertinente.

À réinitialiser ou laisser vide :

- identifiant de la DAE ;
- demandeur, remplacé par l’utilisateur courant ;
- dates de création et de modification ;
- état de validation ;
- étapes, visas et votes ;
- commentaires généraux et commentaires de décision ;
- date d’archivage ;
- date de suppression ;
- résultat du précédent circuit.

Si une référence copiée est inactive ou supprimée, elle doit être laissée vide et signalée au demandeur pour ressaisie.

## 8. Décisions à obtenir pendant la rencontre

- confirmer les rôles et leurs intitulés métier ;
- valider la matrice des droits champ par champ ;
- confirmer qui peut lancer une DAE au nom du demandeur ;
- confirmer les délais, relances et règles de suppléance ;
- choisir la politique et la durée d’archivage ;
- confirmer les données à reprendre dans les anciennes DAE ;
- confirmer les données à recopier ou remettre à zéro lors d’une duplication ;
- identifier les règles de conservation imposées par le métier ou la réglementation.

## 9. Références dans l’application

- ADR 0042 — autorisation au niveau du champ ;
- ADR 0048 — cinq zones fonctionnelles de la DAE ;
- ADR 0050 — périmètres hiérarchiques des utilisateurs ;
- ADR 0055 — workflow de validation ;
- ADR 0056 — remise à zéro exceptionnelle d’un cycle ;
- ADR 0062 — export PDF sécurisé des DAE.
