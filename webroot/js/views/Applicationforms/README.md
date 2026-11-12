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

Le chargement des responsables est désactivé lorsque le champ département est
en lecture seule. Les listes précédentes sont effacées pendant le chargement,
les réponses tardives sont ignorées et une liste vide ou une erreur réseau
reçoit un message français dans la zone d'administration.
