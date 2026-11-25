# Scripts JavaScript des formulaires de DAE

`index.js` pilote les listes des demandes et des archives. La liste courante
masque les DAE archivées par défaut ; sa case « Inclure les demandes archivées
dans la recherche » demande au serveur toutes les DAE visibles. La page
`/applicationforms/archives` ne montre que les DAE archivées. Les deux vues
utilisent la même grille distante, les commandes de ligne de `ButtonFactory`
et les commandes groupées rendues par `ApplicationformsActions`. Les mutations
passent par les endpoints API et leurs Policies serveur.

`applicationform-treeselect.js` initialise l'arbre des départements. Il
actualise les assistantes et les responsables après sélection d'un département.
Les responsables sont lus depuis leurs associations directes au département ;
un responsable associé à un parent n'est pas hérité par ses enfants.

Quand un département n'a qu'une assistante éligible, le script masque le
sélecteur et affiche son nom avec un message d'autorisation. Il transmet aussi
son identifiant par un champ masqué lorsque le champ est modifiable. En lecture
seule, l'option unique déjà rendue par le serveur sert à afficher le même
message, sans activer de champ de soumission.

`applicationform-cgr.js` recharge la configuration CGR à chaque changement de
département. Il saisit directement le CGR lorsqu'une seule combinaison complète
existe, affiche les sélecteurs par segment lorsqu'il y en a plusieurs et rend le
champ libre lorsqu'aucun CGR complet n'est disponible. Un CGR libre existant est
conservé à l'ouverture de l'édition. Les valeurs précédentes sont effacées
pendant le chargement, les réponses tardives sont ignorées et une erreur affiche
un message français. Si l'opérateur ne peut pas créer de DAE, le chargement est
ignoré en édition et la valeur existante reste en lecture seule. Le champ `cgr`
conserve son autorisation serveur existante.

Le chargement des responsables est désactivé lorsque le champ département est
en lecture seule. Les listes précédentes sont effacées pendant le chargement,
les réponses tardives sont ignorées et une liste vide ou une erreur réseau
reçoit un message français dans la zone d'administration.

`applicationform-candidate.js` gère le candidat interne ou externe. Le modèle
concatène prénom et nom dans le champ historique à la sauvegarde; choisir un
collaborateur renseigne ce champ, puis efface et verrouille les champs externes.
Les anciennes demandes sans champs V3 gardent leur saisie unique du nom complet
en édition.

`salary-calculation.js` met à jour l'aperçu du salaire de base dès que la
périodicité et la rémunération sont renseignées. Le nombre de jours est
facultatif et prérempli avec le calcul des jours de semaine, sans traitement
des jours fériés. Pour une rémunération en €/jour sans nombre saisi, le salaire
de base reste vide. Les autres unités prises en charge sont calculées sans
nombre de jours. €/heure n'est pas proposé à la création; les DAE historiques
gardent leur unité et doivent passer à une unité prise en charge pour modifier
leur rémunération.

Le script `zone-contract.js` demande à l'opérateur de choisir entre le nombre
actuel et la nouvelle estimation après un changement de dates. Le zéro demande
confirmation avant l'enregistrement. L'ORM refuse le zéro lorsque la période
contient un jour de semaine; après une sauvegarde réussie à zéro, le notifier
envoie un courriel à l'auteur, aux responsables associés au service et aux
ADMIN rattachés au département.

Un ajustement manuel des jours déclenche le recalcul du salaire. La matrice ACL
autorise la soumission de `working_days` et garde `salary_base` en lecture
seule, car le modèle recalcule ce dernier à l'enregistrement.
