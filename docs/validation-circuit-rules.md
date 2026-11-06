# Administrer les règles locales de circuit

La page **Règles locales de circuit** est accessible depuis la navigation aux
opérateurs autorisés, ainsi que depuis les séquences pour les
super-administrateurs. Les séquences et délais restent dans
`/validationsequences` ; les paramètres globaux dans `/workflow-settings`.

## Déployer

Appliquer les migrations applicatives avec `rtk make db.migrate` depuis la
racine du workspace avant d’ouvrir l’application mise à jour. La migration
`20261106100000_CreateValidationCircuitManagers` crée uniquement la table vide
des habilitations locales. Elle a été validée sur `daetf2_test` ; elle n’a pas
été exécutée sur la base métier dans cette livraison.

## Préparer les accès

1. Un super-administrateur ouvre la section des habilitations locales.
2. Il choisit un rôle gestionnaire et les départements exacts autorisés.
3. Il vérifie les rattachements explicites de l'opérateur dans
   `user_departments`. Une habilitation seule ne suffit pas.

La désignation d'un rôle gestionnaire est indépendante du rôle final d'une
règle. Ni le rôle Admin ni une affectation au seul département parent ne
donnent d'accès implicite. Aucun ancien droit n'est repris automatiquement.

## Saisir les règles de la rédaction SEM

1. Vérifier les séquences et les validateurs rattachés aux seuls services
   concernés. Un validateur doit avoir le rôle attendu et un rattachement
   explicite au service ; l'étape demandeur appartient au créateur de la DAE.
2. Sélectionner tout ou partie des services autorisés dans l'arbre, en
   affinant la sélection des descendants présents. Contrôler la liste choisie.
3. Choisir le type **Facture** et le rôle final **SGR**, puis examiner l'aperçu
   de chaque département avant l'ajout.
4. Refaire la saisie avec **Pige** et **Contrôle de gestion**.

Ces données sont saisies par l'opérateur après livraison ; la migration ne
crée aucune règle réelle et le moteur ne compare aucun de ces libellés.

Un doublon refuse tout l'ajout groupé. Les règles existantes se modifient ou se
retirent individuellement. Le retrait rétablit le circuit complet pour les
futurs lancements. Une configuration invalide bloque l'enregistrement ; ses
messages expliquent les corrections nécessaires. Les rôles parallèles de la
séquence finale sont tous conservés et doivent tous accepter.

## Comprendre les effets

- Une règle s'applique uniquement au couple exact département–contrat.
- Sans règle, le circuit départemental complet reste utilisé.
- Un service créé plus tard ne reçoit aucune règle automatiquement.
- Le rattachement d'un contrôleur de gestion à VM ne crée aucune règle VM.
- L'éligibilité du créateur est revérifiée au lancement pour l'étape demandeur.
- Les défauts apparus depuis l'enregistrement bloquent le lancement, sans
  retour silencieux au circuit complet et sans cycle partiellement créé.
- Les cycles lancés conservent leurs étapes après changement du paramétrage.
- Le département et le contrat restent verrouillés tant qu'un cycle existe,
  même après acceptation ou refus. La remise à zéro exceptionnelle autorisée
  supprime ce cycle et permet de corriger la DAE puis de la relancer.

Les courriels déjà remis avant une remise à zéro ne peuvent pas être retirés.
