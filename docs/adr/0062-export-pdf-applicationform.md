# ADR 0062 : Export PDF sécurisé des DAE

**Date :** 05 octobre 2026
**Statut :** Accepté
**Dépendances :** [ADR 0042](./0042-acl-granulaire-niveau-champ-formulaire.md), [ADR 0047](./0047-normalisation-vues-identity-assets-js.md), [ADR 0052](./0052-presentation-unifiee-erreurs-validation-web-api.md), [ADR 0054](./0054-commandes-ui-autorisees-par-domaine.md), [ADR 0055](./0055-workflow-validation-applicationforms.md)

## Contexte

Les opérateurs doivent pouvoir fournir un document PDF à partir d'une
Applicationform (DAE) sélectionnée. Le document doit être ouvert dans un
nouvel onglet du navigateur et présenter une synthèse adaptée au rôle de
l'opérateur.

La DAE contient des informations de niveaux de confidentialité différents.
L'application dispose déjà de Policies pour l'accès à la fiche et d'un
paramétrage `FieldAuthorizations` pour les droits détaillés sur ses zones et
ses champs. Le PDF ne doit pas introduire une seconde source de vérité pour
ces autorisations.

Le cycle de validation est porté par le workflow immuable défini dans l'ADR
0055. Il doit être rendu sous forme de tableau afin de permettre la lecture
de l'avancement de chaque étape.

## Décision

1. **Point d'entrée Web** : une action protégée du domaine
   `Applicationforms`, exposée selon la convention
   `/applicationforms/viewpdf/{id}`, génère et retourne le PDF de la DAE.
   L'action autorise la ressource avec `ApplicationformPolicy` avant de
   charger ou de rendre les données. Toute DAE hors du périmètre de
   l'opérateur est refusée côté serveur, même si une URL est appelée
   directement.

2. **Ouverture dans le navigateur** : l'action d'interface `viewpdf` existante
   est réutilisée pour construire le lien vers cette route et l'ouvrir avec la
   cible `_blank`. Les templates ne construisent pas directement la route ni
   le bouton métier ; la commande passe par la fabrique d'actions du domaine
   conformément à l'ADR 0054.

3. **Génération du document** : le PDF est généré côté serveur par un service
   dédié et une bibliothèque PDF maintenue, à partir d'un gabarit de
   présentation local. Le navigateur ne réalise pas la conversion HTML/PDF
   et aucune dépendance distante ou CDN n'est utilisée. La réponse utilise le
   type MIME `application/pdf` et un nom de fichier explicite contenant le
   numéro de la DAE.

4. **Droits et contenu par rôle** : le service de génération réutilise les
   autorisations existantes de la fiche, des zones et des champs. Il ne
   définit pas de matrice PDF parallèle par rôle. Les champs non autorisés
   sont absents du document, et non simplement masqués par une règle CSS.
   Le numéro de DAE reste toutefois obligatoire pour tout opérateur autorisé
   à consulter la fiche.

5. **Informations obligatoires** : chaque PDF contient au minimum :
   - le numéro de la DAE, correspondant à `applicationforms.id` ;
   - le libellé du poste (`jobtitle`) ;
   - la date de début lorsqu'elle est renseignée ;
   - la date de fin lorsqu'elle est renseignée ;
   - le service concerné, représenté par le département de la DAE.

6. **Tableau du cycle de validation** : le document présente une ligne par
   étape du cycle immuable courant, dans l'ordre de séquence. Chaque ligne
   expose le rôle, l'opérateur lorsqu'il est connu, le statut, la date de
   validation lorsqu'elle existe et le commentaire final du vote lorsque ce
   commentaire est autorisé pour l'opérateur courant. Les informations
   historiques détaillées hors de cette synthèse, notamment les événements
   techniques de réinitialisation, ne font pas partie du PDF.

7. **Statuts accessibles** : les états du tableau sont rendus avec un code
   couleur et un libellé textuel : vert pour « Accepté », rouge pour
   « Refusé » et orange pour « En attente ». La couleur est un renforcement
   visuel et ne constitue jamais l'unique indication de l'état.

8. **Données du workflow** : les étapes et les votes sont lus depuis le cycle
   de validation immuable et ses associations ou vues existantes. Aucune
   colonne, table, vue SQL ou migration n'est ajoutée pour produire le PDF.

## Justification

La génération côté serveur garantit que l'autorisation est appliquée au
moment de la production du document et empêche de récupérer des champs privés
en modifiant le HTML côté navigateur. La réutilisation des Policies et des
`FieldAuthorizations` respecte le principe DRY et évite qu'un changement de
droits soit oublié dans une matrice PDF distincte.

Un service dédié sépare la collecte sécurisée des données, la décision de
visibilité et la mise en page du contrôleur Web. Le choix d'un gabarit local
et d'une bibliothèque PDF maintenue conserve une implémentation testable et
compatible avec l'architecture CakePHP existante.

Le tableau de validation s'appuie sur l'instantané du workflow défini par
l'ADR 0055. L'ajout d'un libellé à chaque couleur améliore l'accessibilité,
l'impression en niveaux de gris et la compréhension du document.

## Conséquences

### Positives

* Les opérateurs disposent d'un document partageable ouvert dans un nouvel
  onglet.
* Le contenu du PDF respecte les droits applicatifs existants et varie donc
  selon le rôle sans duplication de la politique de sécurité.
* Le numéro de DAE et les informations essentielles sont présents dans tous
  les documents autorisés.
* Le cycle de validation est lisible, imprimable et compréhensible sans
  dépendre exclusivement des couleurs.
* Aucune évolution du schéma de données n'est nécessaire.

### Négatives

* Une bibliothèque PDF et un gabarit de rendu devront être maintenus avec le
  projet.
* Les différences de droits entre opérateurs peuvent produire des PDF de
  longueur et de contenu différents pour une même DAE.
* Le rendu PDF nécessitera des tests spécifiques de contenu, d'autorisation et
  de mise en page.

## Vérifications attendues lors de l'implémentation

* Tester l'accès autorisé et le refus d'une DAE hors périmètre.
* Vérifier le type MIME, le nom du fichier et la présence des informations
  obligatoires.
* Vérifier le contenu produit pour plusieurs rôles et le masquage effectif
  des champs ou commentaires non autorisés.
* Vérifier les lignes du cycle dans les états accepté, refusé et en attente.
* Vérifier l'affichage conjoint du libellé et de la couleur des statuts.
* Ajouter les tests de Policy ou de service au niveau unitaire, et les tests
  de route/réponse PDF au niveau fonctionnel HTTP.
