# ADR 0070 — Circuits courts locaux selon le type de contrat

**Date :** 23 octobre 2026

**Statut :** Accepté

**Dépendances :** [ADR 0005](./0005-modele-de-donnees.md), [ADR 0041](./0041-segregation-donnees-model-custom-finders.md), [ADR 0050](./0050-semantique-perimetres-hierarchiques-utilisateur.md), [ADR 0055](./0055-workflow-validation-applicationforms.md), [ADR 0056](./0056-remise-a-zero-cycle-validation.md), [ADR 0058](./0058-separation-parametrage-global-workflow.md)

## Contexte

GDAETF sert plusieurs sociétés et départements, dont les circuits de
validation peuvent répondre à des contraintes métier différentes. Le cahier
des charges de la rédaction de la SEM demande qu'une DAE de type « Facture »
s'arrête au Secrétariat général de la rédaction (SGR) et qu'une DAE de type
« Pige » s'arrête au Contrôle de gestion. Ces exceptions concernent les
services de cette rédaction, pas toutes les DAE des autres départements ou
sociétés.

L'ADR 0055 prévoit que le lancement copie les séquences du département dans
un cycle dont les étapes déterminent ensuite la progression et l'état final.
Aujourd'hui, toutes les séquences actives du département sont copiées. Le
service n'accorde pas de droit de vote global à un rôle : hors étape
« demandeur », un validateur doit avoir le rôle de l'étape et un rattachement
explicite au département de la DAE dans `user_departments`.

## Décision

### Règles de fin de circuit

Le circuit configuré dans `validationsequences` reste la référence pour chaque
département. Une règle de fin facultative associe un **département de DAE**, un
**type de contrat** et un **rôle final**. Le couple département–type de contrat
est unique. En l'absence de règle, la DAE suit toutes les séquences de son
département. Les règles portent sur les identifiants des référentiels et des
départements ; les libellés « Facture », « Pige », « SGR » ou « SEM » ne sont
pas des conditions codées dans le service.

Pour les services concernés de la rédaction de la SEM, le paramétrage attendu
est le suivant :

| Type de contrat | Rôle final | Effet |
|---|---|---|
| Facture | SGR | Fin après l'acceptation de la séquence du SGR. |
| Pige | Contrôle de gestion | Fin après l'acceptation de la séquence du Contrôle de gestion. |
| Autres types sans règle | Aucun | Circuit départemental complet. |

Le rattachement d'un contrôleur de gestion à un autre département, tel que VM,
n'étend pas cette exception aux DAE de cet autre département. Le Contrôle de
gestion peut intervenir pour tout type de contrat et dans tout département où
son rôle figure dans le circuit et où un utilisateur éligible lui est
explicitement rattaché. Cette décision ne crée aucun droit de vote global.
Le SGR n'intervient comme validateur que dans les circuits et les départements
de la rédaction de la SEM pour lesquels ses utilisateurs sont habilités.

### Administration et périmètres

Une page dédiée `/validation-circuit-rules`, reliée à
`/validationsequences`, permet de consulter, créer, modifier et retirer les
règles. L'écran `/validationsequences` conserve l'affectation des rôles, leur
ordre et leurs délais par département ; `/workflow-settings` conserve les
paramètres globaux. La nouvelle page permet de sélectionner des départements,
un type de contrat et un rôle final, et de prévisualiser les étapes retenues.

Les règles sont enregistrées pour chaque département sélectionné. La
sélection d'un parent peut sélectionner ses descendants présents à cet
instant, sans donner automatiquement une règle à un département créé plus
tard. Elle ne doit pas étendre une exception à toute la SEM lorsque seule sa
rédaction est concernée.

Les super-administrateurs peuvent gérer toutes les règles et désigner, par
département, les rôles autorisés à les administrer. Un utilisateur non
super-administrateur ne peut lire ou modifier que les règles des départements
explicitement associés à son compte dans `user_departments` **et** pour
lesquels son rôle dispose de cette habilitation de gestion. Le rôle SGR est
ainsi habilitable pour les services de sa rédaction sans exiger le rôle Admin
global ni donner le même pouvoir aux autres sociétés.
Le rôle gestionnaire est une donnée de configuration distincte du rôle final
d'une règle ; changer ce dernier ne confère jamais un droit d'administration.
La Policy applique le même périmètre à la page, aux listes, aux mutations API
et aux opérations portant sur plusieurs départements. La visibilité d'un
bouton ne constitue pas une autorisation.

### Lancement et conservation du cycle

Au lancement, le service cherche la règle correspondant exactement au
`contracttype_id` et au `department_id` de la DAE. Si elle existe, il cherche
le rôle final parmi les séquences actives de ce département et retient toutes
les affectations dont le **numéro de séquence** est inférieur ou égal à celui
de ce rôle. Tous les rôles partageant la séquence finale sont conservés ; leur
acceptation est nécessaire pour terminer le cycle.

Une règle explicite dont le rôle final est absent ou inactif dans le circuit
du département bloque le lancement avec un message indiquant le contrat, le
département et le rôle à corriger. Le lancement vérifie également qu'un
validateur éligible existe pour chacune des étapes effectivement retenues.
Une configuration invalide ne provoque pas de retour silencieux au circuit
complet.

Le lancement copie uniquement les étapes retenues dans le cycle. Lorsque tous
les rôles de sa dernière séquence ont accepté, la DAE est considérée comme
acceptée selon le mécanisme existant ; un refus conserve son effet habituel.
Les modifications ultérieures des règles ou des séquences ne réécrivent pas
les cycles déjà lancés. Le type de contrat et le département d'une DAE sont
verrouillés tant qu'un cycle existe, sous réserve de la remise à zéro
exceptionnelle déjà autorisée par l'ADR 0056.

## Justification

La règle locale réutilise le circuit départemental et l'instantané décidés
par l'ADR 0055. Elle évite de créer et maintenir un circuit complet parallèle
pour chaque type de contrat. L'identification par rôle suit les changements
d'ordre des séquences, tandis que l'enregistrement explicite par département
empêche qu'une exception propre à une rédaction s'applique à une autre société.
La séparation des droits d'administration et de validation préserve les
périmètres existants.

## Conséquences

- Une évolution de schéma et une interface d'administration sont nécessaires
  pour les règles et leurs habilitations locales. Leurs références doivent
  être contrôlées côté serveur ; aucune modification de vue SQL n'est requise
  par la présente décision.
- Les séquences et les rattachements `user_departments` du Contrôle de gestion
  doivent être configurés dans chaque département où l'organisation souhaite
  son intervention. Le même contrôle vaut pour le SGR dans sa rédaction.
- Une règle portant sur un département nouvellement créé doit être ajoutée
  explicitement. Les cycles déjà lancés conservent leurs étapes copiées.
- Les vérifications doivent couvrir les parcours courts et complets, les
  frontières entre sociétés, les rôles parallèles, les configurations
  invalides, l'acceptation finale et les droits d'administration locaux.
- Le modèle général d'habilitations de l'ADR 0069 est encore proposé. La
  présente décision définit le droit local nécessaire sans appliquer par
  anticipation l'ensemble de cet ADR ; leur articulation sera vérifiée avant
  une éventuelle généralisation du modèle d'habilitations.

Cette décision amende le choix du parcours systématiquement complet de l'ADR
0055 sans modifier rétroactivement ce document. Elle n'étend pas aux circuits
courts les autres demandes du cahier des charges, notamment l'archivage ou
les notifications spécifiques.
