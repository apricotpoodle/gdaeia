# ADR 0012 : Modélisation manuelle des relations bidirectionnelles multiples

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'outil d'automatisation `bake` échoue systématiquement (`DatabaseException`) lorsqu'il rencontre deux tables liées par de multiples clés étrangères croisées (ex: `departments` possède plusieurs `cgr_codes`, et utilise un `cgr_code` par défaut). L'outil tente d'affecter le même alias de relation aux deux liens.

## Décision
Pour les tables présentant ce motif architectural complexe, la classe `Table` de l'ORM doit être écrite ou corrigée manuellement.
Des alias sémantiques uniques doivent être définis pour chaque relation (ex: `OwnedCgrCodes` et `DefaultCgrCode` dans `DepartmentsTable.php`).

### Point de vigilance : Conventions de nommage et Entités
Lors de la création manuelle d'une classe Table pour contourner le crash, il est impératif de respecter la convention de pluralisation de CakePHP (ex: le fichier DOIT se nommer `DepartmentsTable.php`). 
De plus, la création manuelle de la Table n'entraîne pas la création de l'Entité associée. Il faut exécuter la commande générique `bin/cake bake model <NomAuPluriel>` (ex: `bin/cake bake model Departments`). L'outil détectera que la Table existe déjà (et la préservera), mais générera l'Entité et les Fixtures manquantes, évitant ainsi les erreurs d'analyse statique dans l'IDE.

## Justification
On n'altère pas un schéma de base de données parfaitement normalisé pour satisfaire les limites d'un outil de génération de code. La création manuelle de ces classes spécifiques permet à `bake` de les ignorer par la suite et de reprendre sereinement la génération du reste de l'ORM.