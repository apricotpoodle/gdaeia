# Scripts JavaScript des formulaires de DAE

`applicationform-treeselect.js` initialise l'arbre des départements. Il
actualise les assistantes et les responsables après sélection d'un département.
Les responsables sont lus depuis leurs associations directes au département ;
un responsable associé à un parent n'est pas hérité par ses enfants.

Le chargement des responsables est désactivé lorsque le champ département est
en lecture seule. Les listes précédentes sont effacées pendant le chargement,
les réponses tardives sont ignorées et une liste vide ou une erreur réseau
reçoit un message français dans la zone d'administration.
