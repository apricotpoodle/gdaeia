# Scripts JavaScript des formulaires de DAE

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
