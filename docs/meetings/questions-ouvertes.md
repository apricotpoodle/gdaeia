# Questions métier ouvertes

Ce tableau prépare les prochaines réunions métier. Pendant la réunion,
l'opérateur humain complète uniquement les colonnes **Réponse métier du
client** et **Exemple métier donné par le client**, sans interpréter la
réponse. Il met aussi à jour la colonne **Statut** selon le cycle ci-dessous.
La colonne **Cadrage du plan approuvé** consigne les directives du projet.
Elle ne clôture pas les questions dont la réponse métier reste attendue.

Les questions sont regroupées par ticket. Les questions Q-0018-02 à
Q-0018-04, encore ouvertes après la livraison technique du ticket 0018, sont
également des entrées du ticket [0030](../backlog/0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md).
Les réponses métier à Q-0018-01, Q-0018-05 et Q-0018-06 sont consignées
ci-dessous.
Le code décrit dans les tickets terminés ne remplace pas une réponse métier.
Les tickets principaux 0018 à 0023 et les tickets du sous-workflow
validation-applicationform à partir de 0018 restent couverts.

Le plan retient aussi une recette transversale dans Docker local isolé, avec
des données synthétiques. L'opérateur configure les séquences par service,
sans ordre des rôles codé en dur. La configuration d'Airflow et de la
production relève de l'exploitation. Voir le
[ticket 0039](../backlog/0039-configurer-et-recetter-gdae-v3.md).

## Cycle de statut

- **À poser** : la question reste à aborder et aucune réponse n'est consignée.
- **À clarifier** : la réponse consignée est partielle ou ambiguë, ou la
  décision est reportée. La question reste à trancher.
- **Répondu** : la réponse et tout exemple donné sont consignés, et la décision
  métier est complète.
- **Répercuté** : la décision complète a été reportée dans les documents ou
  tickets concernés, ou sa présence dans ceux-ci a été vérifiée. Ce statut ne
  signifie pas que le code est livré.

Pendant la réunion, passer à **Répondu** lorsque la décision est complète.
Une réponse partielle, ambiguë ou reportée reste **À clarifier**. Après la
réunion, passer à **Répercuté** une fois la décision reportée ou sa présence
vérifiée dans les documents ou tickets concernés.

Les questions et leurs réponses sont conservées comme historique : ne
supprimer aucune ligne du tableau.

| ID | Ticket | Statut | Question | Réponse métier du client | Exemple métier donné par le client | Cadrage du plan approuvé |
|---|---|---|---|---|---|---|
| Q-0018-01 | 0018 | Répercuté | Pour quels types de contrat le champ « Nombre de jours ouvrés » doit-il être visible ? | Le champ est visible dès que les dates de début et de fin sont valides et que la date de fin n'est pas désactivée. Seul le code `cdi` n'a pas de date de fin ; les codes `cdd`, `interim`, `appr.`, `stage`, `pige` et `fact` en ont une. Stage, pige et facture suivent la même règle de jours travaillés que les autres contrats à durée déterminée. |  |  |
| Q-0018-02 | 0018 | Répercuté | Une personne peut-elle saisir manuellement 0 jour ouvré pour une période qui contient des jours de semaine ? | Non. Le zéro est refusé dès que la période contient au moins un jour de semaine; il est accepté pour une période sans jour de semaine. |  |  |
| Q-0018-03 | 0018 | Répercuté | La saisie du nombre de jours est-elle obligatoire ? Le nombre de jours proposé automatiquement suffit-il pour envoyer la DAE en validation, ou la personne doit-elle le vérifier et le corriger si nécessaire ? Exemple à présenter : du 2 au 31 mars 2026, le système propose 22 jours. Peut-on envoyer la DAE avec cette valeur, ou faut-il vérifier le nombre réel de jours travaillés et le corriger si besoin ? | La saisie est facultative. La valeur calculée est préremplie, puis l’opérateur peut la corriger ou l’omettre. Sans nombre saisi pour une rémunération en €/jour, le salaire de base reste vide. |  |  |
| Q-0018-04 | 0018 | Répercuté | Les jours fériés doivent-ils être exclus du calcul des jours ouvrés ? | Aucun calcul particulier des jours fériés n’est appliqué. Un jour férié tombant en semaine compte comme un jour de semaine. |  |  |
| Q-0018-05 | 0018 | Répercuté | Une période composée uniquement d'un samedi et d'un dimanche doit-elle être refusée ? | Non, une telle période est acceptée. Toute valeur de zéro jour ouvré déclenche un avertissement visuel avant l'enregistrement. Le courriel est envoyé uniquement après l'enregistrement réussi, à l'auteur de la saisie, à tous les responsables associés au service et à tout rôle ADMIN qui voit ce service. |  |  |
| Q-0018-06 | 0018 | Répercuté | Que doit-il se passer lorsque les dates changent après la saisie du nombre de jours ouvrés ? | Avertir l'opérateur en indiquant la valeur actuelle du nombre de jours ouvrés et le nouveau calcul. L'opérateur décide de l'action à mener. |  |  |
| Q-0019-01 | 0019 | À poser | Quelles sont les quatre zones qui doivent apparaître dans la vue de consultation d'une DAE ? |  |  |  |
| Q-0019-02 | 0019 | À poser | La vue de consultation doit-elle reprendre exactement l'ordre et les libellés de la vue d'édition ? |  |  |  |
| Q-0019-03 | 0019 | À poser | Quels champs doivent rester masqués selon le rôle de l'utilisateur dans la vue de consultation ? |  |  |  |
| Q-0019-04 | 0019 | À poser | La vue de consultation doit-elle afficher les informations du workflow, les commentaires et l'historique ? |  |  |  |
| Q-0020-01 | 0020 | À poser | Quels champs doivent être copiés lors de la duplication d'une DAE ? |  |  |  |
| Q-0020-02 | 0020 | À poser | Quels champs administratifs, de validation ou d'audit doivent impérativement être effacés ? |  |  |  |
| Q-0020-03 | 0020 | À poser | La nouvelle DAE doit-elle toujours être créée comme brouillon appartenant à l'utilisateur courant ? |  |  |  |
| Q-0020-04 | 0020 | À poser | Que doit faire l'application lorsqu'une référence copiée est inactive ou supprimée ? |  |  |  |
| Q-0021-01 | 0021 | À poser | Quelles permissions génériques doivent être disponibles dans l'application ? |  |  |  |
| Q-0021-02 | 0021 | À poser | Comment les droits du rôle et les droits liés au périmètre doivent-ils se combiner ? |  |  |  |
| Q-0021-03 | 0021 | À poser | Qui peut administrer l'arbre des départements et les rattachements des utilisateurs ? |  |  |  |
| Q-0021-04 | 0021 | À poser | Les droits doivent-ils s'appliquer aux départements descendants ou uniquement au département directement rattaché ? |  |  |  |
| Q-0022-01 | 0022 | À poser | Quelle est la liste officielle des départements à créer, modifier, désactiver ou supprimer ? |  |  |  |
| Q-0022-02 | 0022 | À poser | Quelle organisation hiérarchique doit être retenue pour les branches SEM et Rédac ? |  |  |  |
| Q-0022-03 | 0022 | À poser | Comment traiter les départements existants qui ne correspondent plus au référentiel métier ? |  |  |  |
| Q-0022-04 | 0022 | À poser | Qui valide la nouvelle structure des départements avant son import dans l'application ? |  |  |  |
| Q-0023-01 | 0023 | À poser | Quelle est l'instance MySQL cible et à quelle date la migration doit-elle être réalisée ? |  |  |  |
| Q-0023-02 | 0023 | À poser | Quelles données doivent être migrées et quelles données doivent être archivées ou exclues ? |  |  |  |
| Q-0023-03 | 0023 | À poser | Quelle durée d'indisponibilité est acceptable pendant la migration ? |  |  |  |
| Q-0023-04 | 0023 | À poser | Quels contrôles métier doivent être réalisés avant d'autoriser la reprise de l'application ? |  |  |  |
| Q-0030-01 | 0030 | Répercuté | Le taux €/heure doit-il être conservé ? Si oui, le salaire de base doit-il rester un taux horaire ou être converti en montant mensuel ? En cas de conversion, quel nombre d'heures faut-il utiliser : une valeur fixe ou celle du temps de travail choisi ? Proposition à discuter : supprimer €/heure si cette unité n'est pas utilisée. Exemple : pour 12 €/h, quelle valeur le salaire de base doit-il afficher ? |  |  | Retirer €/heure des nouvelles saisies et conserver l’historique. Toute modification de rémunération historique doit passer par une unité prise en charge. |
| Q-0030-02 | 0030 | Répercuté | Faut-il développer une action dédiée pour récupérer une DAE transmise, ou les mécanismes existants suffisent-ils : annuler et réinitialiser le cycle, puis le relancer, et faire outrepasser par un administrateur habilité les étapes en attente ou à venir pour retrouver un état intermédiaire ? Proposition à discuter : utiliser les mécanismes existants si le métier accepte que la réinitialisation supprime les votes et les étapes du cycle, sans en garder la piste d'audit applicative. La DAE et ses données métier restent conservées. | Le plan approuvé suffit : la remise à zéro suivie d'une relance est acceptée. Le métier accepte la suppression des votes et étapes du cycle sans piste d'audit applicative; la DAE et ses données restent conservées. |  | Conserver la remise à zéro et la relance existantes. Limiter la remise à zéro aux cycles qui n’ont pas atteint leur dernière séquence configurée. Voir l’[ADR 0076 accepté](../adr/0076-limiter-remise-a-zero-avant-derniere-sequence.md). |
| Q-0030-03 | 0030 | À clarifier | L'archivage est-il déclenché manuellement ou automatiquement, et après quel délai ? Quelle durée de conservation appliquer ? Quels rôles et périmètres peuvent rechercher ou consulter les archives ? Proposition à discuter : réserver la colonne « Archivé » et l'accès aux DAE archivées aux superusers et aux utilisateurs ADMIN dans leur périmètre ; les autres rôles ne verraient que les DAE non archivées. Cette solution convient-elle ? | L'archivage concerne tous les types de DAE, individuellement ou en masse, et doit être réversible. La liste affiche les DAE non archivées par défaut, avec une possibilité de rechercher parmi toutes les DAE, dans le respect des droits d'accès. Pour le moment, seuls un superuser et un utilisateur de rôle ADMIN qui voit le service de la DAE peuvent archiver ou désarchiver. |  | L’archivage est manuel et réversible. Il exclut les cycles actifs, conserve les droits habituels de lecture et ne déclenche aucune purge automatique. Le délai d’éligibilité éventuel et la durée de conservation restent à préciser. |
| Q-0030-04 | 0030 | Répercuté | Qui reçoit les notifications de validation, les rappels quotidiens, les notifications de DAE acceptée et la synthèse hebdomadaire ? Quelles commandes l'application fournit-elle pour les envois récurrents ? | Tout changement d'état du cycle de validation déclenche une notification en temps réel aux rôles concernés. Pour une DAE bloquée, les rôles pouvant outrepasser le blocage sont également notifiés. La liste quotidienne des demandes en attente est adressée aux rôles concernés par l'étape ; le rappel quotidien est prévu à 07 h 00. À la fin du cycle, une DAE acceptée (« signée ») déclenche une notification à l'assistante qui l'a saisie, à tous les responsables du service et à tout rôle ADMIN qui voit le service ; ces ADMIN sont considérés comme des responsables du service. La synthèse hebdomadaire des DAE entièrement validées est adressée à tout rôle ADMIN qui voit le service des DAE concernées. L'application fournit une commande CLI et une commande Make pour lancer les envois récurrents ; Airflow planifie les traitements CLI au moyen de DAG. Le jour et l'heure de la synthèse hebdomadaire sont configurés dans Airflow, hors du périmètre de l'application. |  | La recette locale ne configure ni Airflow ni la production. |
| Q-0030-05 | 0030 | Répercuté | Quelles résolutions, tailles de fenêtre et facteurs de zoom définissent l'objectif « voir l'ensemble des champs sans ascenseur » ? | Écran de référence : 1680 × 1050. Contrôle dans une fenêtre non maximisée, avec une zone de page de 1440 × 900 et un contrôle complémentaire de 1366 × 768. Zoom navigateur : 100 % pour les deux tailles. |  | Vérifier les dimensions de la réponse métier dans Chrome, Edge et Firefox récents et consigner les versions utilisées lors de la recette. |
| Q-0030-06 | 0030 | Répercuté | Le champ `manager_id` de l'Applicationform doit-il rester en base, être utilisé par l'application ou être migré ? Comment l'articuler avec les associations multiples de responsables des départements ? | Ne pas modifier la structure de la table ni les valeurs existantes de `applicationforms.manager_id` ; elles sont conservées sans migration et le champ est ignoré par l'application. Les responsables sont déterminés à partir des associations multiples du service. Le nom `current_manager_id` désigne l'ancienne colonne de `departments`, distincte de `applicationforms.manager_id`. |  |  |
| Q-0030-07 | 0030 | À poser | Quels champs additionnels doivent figurer dans le PDF des DAE ? Le dossier retient actuellement la validation du Contrôle de gestion visible sur deux lignes. |  |  |  |
| Q-0040-01 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `hr_description` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions : le réglage est global et modifiable uniquement par un super-administrateur. Ces six champs sont configurables et tous réinitialisés par défaut. Une liste vide est invalide ; en l'absence de configuration ou si elle est invalide, l'application applique la liste sûre actuelle. Le réglage concerne les nouvelles duplications uniquement. Les horodatages `created` et `modified` suivent le comportement standard, sans journal d'audit dédié. |
| Q-0040-02 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `control_management_validation` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions, consigné dans Q-0040-01. |
| Q-0040-03 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `final_argument` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions, consigné dans Q-0040-01. |
| Q-0040-04 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `budgetfeature_id` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions, consigné dans Q-0040-01. |
| Q-0040-05 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `yesno_id` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions, consigné dans Q-0040-01. |
| Q-0040-06 | 0040 | Répercuté | Lors de la duplication d'une DAE, faut-il vider `qualification` ou reprendre sa valeur ? | Vider. |  | Cadrage commun aux six questions, consigné dans Q-0040-01. |
| Q-0041-01 | 0041 | À clarifier | L'accès à la consultation et à la gestion des permissions par rôle doit-il rester réservé aux super-administrateurs, comme le demande le critère 1 du ticket, ou peut-il être délégué aux opérateurs dont le rôle reçoit `menus.manage` sur `unscoped` ? Arbitrage prévu le 21 novembre 2026. Les scénarios et leurs conséquences sont détaillés ci-dessous. |  |  |  |
| Q-VAF-0018-01 | validation-applicationform/0018 | À poser | Dans quels cas un cycle de validation peut-il être annulé par un utilisateur ou un administrateur ? |  |  |  |
| Q-VAF-0018-02 | validation-applicationform/0018 | À poser | Une annulation doit-elle conserver l'historique des votes et des commentaires ? |  |  |  |
| Q-VAF-0018-03 | validation-applicationform/0018 | À poser | Qui peut réinitialiser un cycle annulé et quelles données doivent être conservées ? |  |  |  |
| Q-VAF-0019-01 | validation-applicationform/0019 | À poser | Dans quel cas une séquence de validation peut-elle être créée sans rôle configuré ? |  |  |  |
| Q-VAF-0019-02 | validation-applicationform/0019 | À poser | Qui doit être autorisé à voter lorsqu'une séquence ne possède aucun rôle explicite ? |  |  |  |
| Q-VAF-0020-01 | validation-applicationform/0020 | À poser | Quels paramètres du workflow doivent être globaux et lesquels doivent être propres à un département ? |  |  |  |
| Q-VAF-0020-02 | validation-applicationform/0020 | À poser | Une modification du paramétrage doit-elle affecter les cycles déjà lancés ? |  |  |  |
| Q-VAF-0020-03 | validation-applicationform/0020 | À poser | Qui peut modifier le paramétrage et quelles validations sont nécessaires ? |  |  |  |
| Q-VAF-0021-01 | validation-applicationform/0021 | À poser | Que doit-il se passer lorsque deux utilisateurs du même rôle votent presque simultanément ? |  |  |  |
| Q-VAF-0021-02 | validation-applicationform/0021 | À poser | Le second vote doit-il être refusé, remplacé ou conservé comme information d'audit ? |  |  |  |

## Dossier de préparation — Q-0041-01 : accès aux permissions par rôle

**Décision attendue :** 21 novembre 2026. La question reste **À clarifier**
jusqu'à ce que l'arbitrage soit consigné dans les colonnes de réponse du
tableau. Les exemples ci-dessous décrivent des scénarios à examiner ; ils ne
constituent ni une réponse du client ni une décision déjà prise.

### Règle actuelle

- `unscoped` est une valeur de périmètre portée par une attribution à un rôle.
  Elle ne donne pas, à elle seule, une permission et ne signifie pas
  `all`. Elle indique qu'aucun filtre générique `self`, `assigned` ou `all`
  n'est appliqué ; la Policy doit encore autoriser l'action.
- Le super-administrateur contourne les attributions via
  `PermissionResolver`. Pour les autres opérateurs, `PermissionPolicy` autorise
  à la fois la consultation (`canIndex`) et la gestion (`canManage`) si leur
  rôle possède `menus.manage` sur `unscoped`.
- L'attribution est au rôle, pas à un utilisateur individuel : tout opérateur
  non-super-administrateur ayant ce rôle satisfait donc la même condition.
  `MenuPolicy` utilise aussi `menus.manage/unscoped` pour administrer les
  menus.
- L'ADR 0069 indique que `menus.manage` peut avoir le périmètre `unscoped`,
  mais que la matrice actuelle ne l'attribue à aucun rôle ordinaire. Le ticket
  0041 décrit également les rôles ordinaires sans cette attribution. Un test
  API ajoute volontairement cette attribution au rôle Admin pour montrer
  qu'un opérateur non-super-administrateur ainsi habilité est accepté ; ce
  scénario de test ne signifie pas que la matrice réelle l'accorde.
- Le périmètre organisationnel courant est le département (`assigned`).
  L'écran et l'API des permissions ne filtrent toutefois ni par département
  ni par tenant : la liste du catalogue et des attributions est globale.

### Scénarios à comparer

1. **Réserver l'accès aux super-administrateurs.** Un opérateur du rôle Admin
   (rôle 1) ou d'un autre rôle ordinaire reçoit un refus, même si son rôle
   possède `menus.manage/unscoped`. Seul un compte marqué super-administrateur
   accède à l'écran et à l'API. Ce choix correspond au critère 1 du ticket
   0041 et exclut une délégation ordinaire.
2. **Autoriser une délégation globale.** Si `menus.manage/unscoped` est ajouté
   au rôle Admin ou à tout autre rôle, chacun de ses membres non
   super-administrateurs peut consulter le catalogue complet, les attributions
   de tous les rôles, les périmètres admissibles et les URLs des menus. Par
   exemple, un opérateur associé seulement au département A verrait aussi les
   attributions utilisées dans le département B : aucune séparation par
   département ou tenant ne s'applique ici. La même Policy protège les routes
   API d'ajout et de retrait ; malgré une page en lecture seule, cet opérateur
   pourrait donc appeler directement ces mutations. Il obtiendrait aussi
   l'accès d'administration des menus protégé par la même permission.
3. **Déléguer par département ou tenant.** Le `unscoped` actuel ne permet pas
   de limiter cette administration au département A ou à un tenant donné. Si
   une délégation locale est souhaitée, il faudra définir séparément le
   périmètre et la règle Policy/API correspondants avant d'attribuer un droit.

La décision doit donc préciser si la délégation est interdite, globale pour
les membres des rôles autorisés, ou limitée par un périmètre local à concevoir.
Elle doit aussi préciser si la lecture et les mutations d'attribution suivent
la même règle d'accès.

## Règles de saisie pendant la réunion

- Ne pas reformuler la réponse du client.
- Reproduire les termes métier employés pendant la réunion.
- Ajouter les exemples concrets dans la colonne dédiée.
- Si la réponse est partielle, ambiguë ou reportée, la consigner telle quelle
  et conserver le statut « À clarifier ».
- Ne pas remplir la colonne « Statut » avec une décision technique.
- Toute interprétation ou proposition de ticket doit être produite dans un
  document séparé après la réunion.
