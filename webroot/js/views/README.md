# Scripts des vues

Les modules de `References/`, `Roles/` et `Departments/` gardent la
navigation et les mutations de grilles dans les fabriques Tabulator et leurs
observateurs. Les formulaires exposent `base` au superadministrateur :
création d'une ligne socle ou basculement isolé d'une ligne existante. Les
autres champs d'une ligne qui reste socle sont en lecture seule. Dans le
formulaire des départements, les choix d'association sont immuables pendant
un basculement de `base`.

L'interface reflète les droits fournis par le serveur ; les Policies, l'ACL de
champ et le Behavior ORM font autorité sur les requêtes. Aucune colonne
`base` supplémentaire n'est prévue dans les grilles. Voir
l'[ADR 0075 proposé](../../../docs/adr/0075-protection-references-socles.md).

Sur `/permissions`, `Permissions/index.js` attend le choix d’un rôle actif
avant de créer la grille distante en lecture seule. Chaque changement de rôle
réinitialise les filtres et la pagination. La grille affiche le catalogue, les
périmètres admissibles, les attributions du rôle et les URLs des menus liés.
Une permission sans attribution affiche « Non attribuée ».
