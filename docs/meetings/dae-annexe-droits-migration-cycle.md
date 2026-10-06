# Annexe — droits, migration et duplication des DAE

Cette annexe accompagne la [note de préparation de la rencontre](dae-propositions-reunion.md). Elle sert de support de discussion et doit être complétée après validation métier.

## 1. Légende de la matrice

| Code | Signification |
|---|---|
| — | Aucun accès ou donnée non applicable. |
| V | Consultation. |
| M | Modification. |
| M* | Modification conditionnelle : uniquement avant lancement, dans le périmètre autorisé ou pendant l’étape prévue. |
| A | Administration complète, sous réserve de la visibilité et de la Policy serveur. |

La matrice utilise volontairement des noms d’objets métier compréhensibles en réunion. Les noms techniques des colonnes restent une préoccupation d’implémentation et ne constituent pas le vocabulaire fonctionnel proposé.

## 2. Synthèse par zone fonctionnelle

| Zone | Demandeur | Valideurs | Administrateur | Proposition |
|---|---:|---:|---:|---|
| Administration de la demande | M* | V | A | Le demandeur prépare l’identité de la demande ; l’administrateur intervient seulement dans son périmètre. |
| Contrat et recrutement | M* | V | A | Les données sont saisies avant lancement et consultées ensuite par les validateurs. |
| Rémunération et financement | M* | V | A | Les valideurs concernés contrôlent les montants et le financement. |
| Informations réservées | — ou M* selon besoin | V | A | Les accès doivent être explicitement validés, car ces informations sont sensibles. |
| Commentaires | M* | M* pour la décision | A | Les commentaires généraux et les commentaires de validation restent distincts. |

## 3. Matrice détaillée proposée

Les rôles de validation sont regroupés lorsque leur droit est identique. La réunion peut ensuite ajouter une exception propre à un rôle.

| Objet métier de la DAE | Demandeur avant lancement | Valideur de pôle | Valideur RRH / DRH | Valideur contrôle de gestion | Valideur direction | Administrateur |
|---|---:|---:|---:|---:|---:|---:|
| Service demandeur | M* | V | V | V | V | A |
| Personne demandeuse | V | V | V | V | V | A |
| Type de contrat | M* | V | V | V | V | A |
| Motif de recrutement | M* | V | V | — | V | A |
| Motif de remplacement | M* | V | V | — | V | A |
| Source de financement | M* | V | V | M* | V | A |
| Intitulé du poste | M* | V | V | — | V | A |
| Catégorie professionnelle | M* | V | V | — | V | A |
| Temps de travail | M* | V | V | — | V | A |
| Répartition du temps de travail | M* | V | V | — | V | A |
| Rémunération brute | M* | V | V | M* | V | A |
| Périodicité de rémunération | M* | V | V | M* | V | A |
| Qualification requise | M* | V | V | — | V | A |
| Date prévisionnelle de début | M* | V | V | — | V | A |
| Date prévisionnelle de fin | M* | V | V | — | V | A |
| Candidat ou collaborateur pressenti | M* | V | V | — | V | A |
| Indicateur de remplacement | M* | V | V | — | V | A |
| Informations réservées RH | — | — | M* | — | V | A |
| Commentaire général | M* | M* | M* | M* | M* | A |
| Décision de validation | — | M* | M* | M* | M* | A selon procédure |
| État et historique du circuit | V | V | V | V | V | A |
| Dates d’audit et d’archivage | — | — | — | — | — | A |

### Points à confirmer

- Le demandeur peut-il modifier une DAE après son lancement, ou doit-il demander une réouverture ?
- Un valideur peut-il corriger une donnée, ou doit-il uniquement demander une correction ?
- Les informations réservées RH sont-elles nécessaires dans la première version ?
- Le contrôle de gestion peut-il modifier un montant ou seulement l’accepter/refuser ?
- Le candidat ou collaborateur doit-il être visible par tous les validateurs ?
- Les administrateurs disposent-ils tous du même niveau d’accès ?

## 4. Cycle détaillé et états proposés

| État métier | Qui agit ? | Actions possibles | Passage suivant |
|---|---|---|---|
| Brouillon | Demandeur, administrateur habilité | Créer, modifier, enregistrer, supprimer avant lancement | Lancement ou abandon |
| En attente de lancement | Demandeur | Vérifier et lancer | Première séquence |
| En cours de validation | Valideurs de la séquence | Consulter, accepter, refuser avec commentaire | Séquence suivante, refus ou relance |
| En retard | Valideurs concernés, administrateur habilité | Valider, relancer, suppléer après échéance | Suite normale du circuit |
| Acceptée | Utilisateurs autorisés en consultation | Consulter, exporter, archiver selon règle | Archivage logique |
| Refusée | Utilisateurs autorisés en consultation | Consulter le motif, éventuellement dupliquer | Archivage logique |
| Archivée | Utilisateurs autorisés | Consulter, exporter, dupliquer | Nouvelle DAE indépendante |

Le lancement crée un instantané des séquences et des rôles applicables au service. Les votes sont rattachés à cet instantané afin qu’une modification ultérieure de la configuration ne réécrive pas l’historique.

## 5. Plan de reprise des anciennes tables

### 5.1 Inventaire à demander

| Domaine historique | Informations recherchées | Contrôle attendu |
|---|---|---|
| Comptes | Identifiant, nom, prénom, courriel, statut, dates | Un compte cible par personne, sans doublon non justifié. |
| Rôles | Code, libellé, rôle principal, historique éventuel | Chaque rôle est rapproché d’un rôle cible ou placé en anomalie. |
| Services | Code, libellé, hiérarchie, état | Les services actifs sont rapprochés ; les services supprimés sont conservés comme historique si nécessaire. |
| Affectations | Utilisateur, service, période, service principal éventuel | Tous les rattachements utiles sont repris, pas uniquement le service principal. |
| DAE | Demandeur, service, données métier, état, dates | Chaque DAE est rattachée à un utilisateur et à un service cible. |
| Validations | Étape, rôle, personne, décision, date, commentaire | L’historique est conservé sans être transformé en votes actifs. |

### 5.2 Règles de rapprochement proposées

1. Rapprocher automatiquement par identifiant historique lorsque la correspondance est fiable.
2. À défaut, utiliser le courriel normalisé.
3. À défaut, utiliser nom et prénom avec contrôle manuel.
4. Ne jamais fusionner automatiquement deux personnes uniquement sur le nom.
5. Signaler les utilisateurs sans rôle, les rôles inconnus et les services sans équivalent.
6. Conserver un rapport des décisions manuelles de rapprochement.
7. Rendre la migration rejouable sans créer de doublons.

### 5.3 Contrôles avant mise en production

- nombre d’utilisateurs source et cible ;
- nombre de doublons fusionnés et de rapprochements manuels ;
- nombre d’utilisateurs par rôle ;
- nombre de rattachements par service ;
- nombre de DAE par état ;
- nombre de DAE sans demandeur ou service ;
- échantillon de DAE contrôlé par un référent métier ;
- vérification que les anciennes validations sont consultables mais ne relancent aucun circuit.

## 6. Règles de duplication

### Copie proposée

| Objet métier | Règle |
|---|---|
| Référentiels actifs | Copier la référence si elle existe encore et est active. |
| Données de recrutement | Copier pour éviter la ressaisie. |
| Demandeur | Remplacer par l’utilisateur qui déclenche la duplication. |
| Service | Copier si actif et visible par le nouvel utilisateur ; sinon demander une nouvelle sélection. |
| Candidat ou collaborateur | Copier seulement après validation de la pertinence métier. |
| Commentaires | Ne pas copier. |
| Votes et visas | Ne pas copier. |
| Étapes de validation | Ne pas copier ; elles seront recréées au prochain lancement. |
| États, dates d’audit et archivage | Réinitialiser. |

### Garanties attendues

- la DAE source reste inchangée ;
- la nouvelle DAE est un brouillon ;
- aucune validation ne peut être considérée comme acquise par héritage ;
- les champs obligatoires sont contrôlés comme lors d’une création normale ;
- les références inactives sont signalées plutôt que copiées silencieusement ;
- l’opération est journalisée avec l’identifiant de la DAE source et de la nouvelle DAE.

## 7. Livrables après la réunion

Après validation des points métier, préparer séparément :

1. une table de correspondance entre l’ancienne base et le modèle cible ;
2. une migration de reprise testable et rejouable ;
3. la configuration initiale des autorisations par rôle ;
4. les règles d’archivage et de conservation ;
5. la spécification de la duplication ;
6. les ADR nécessaires si une décision modifie l’architecture acceptée.
