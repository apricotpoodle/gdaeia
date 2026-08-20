# ADR 0047 : Découpage de l'IHM Applicationform en 5 zones fonctionnelles

## Statut
Accepté

## Contexte
Le formulaire et la vue de l'entité `Applicationform` comportent de nombreux champs. Pour améliorer l'expérience utilisateur et garantir la confidentialité des données, l'IHM doit être structurée en 5 zones distinctes dont la visibilité/accessibilité dépend des rôles utilisateurs. De plus, la zone dédiée aux commentaires doit maximiser l'espace utile à l'écran.

## Décisions
1. **Zones fonctionnelles** : Définition de 5 zones au niveau du modèle/champ de l'entité :
   - `admin` (département, utilisateur, dates...)
   - `contrat` (type de contrat, motif de recrutement, catégorie pro, etc.)
   - `rémunération` (rémunération brute, fréquence/période)
   - `réservés` (champs d'administration RH ou validation interne)
   - `commentaires` (fil de discussion polymorphique)
2. **Gestion de l'espace pour les commentaires** :
   - Utilisation d'un panneau latéral repliable (Offcanvas/Drawer) ou d'un onglet dédié compact avec compteur pour réduire la surface occupée sur l'écran principal.
3. **Ancrage dans l'entité `Applicationform`** :
   - Centralisation des identifiants/clés des zones sous forme de constantes publiques dans l'entité `Applicationform`.

## Conséquences
- Lisibilité accrue et contrôle d'accès granulaire par zone/rôle.
- Interface épurée nécessitant moins de défilement vertical.