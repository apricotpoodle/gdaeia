# Questions métier ouvertes

Ce tableau prépare les prochaines réunions métier. Pendant la réunion,
l'opérateur humain complète uniquement les colonnes **Réponse métier du
client** et **Exemple métier donné par le client**, sans interpréter la
réponse.

Les questions sont regroupées par ticket. Les tickets principaux 0018 à 0023
et les tickets du sous-workflow validation-applicationform à partir de 0018
sont couverts.

| ID | Ticket | Statut | Question | Réponse métier du client | Exemple métier donné par le client |
|---|---|---|---|---|---|
| Q-0018-01 | 0018 | À poser | Pour quels types de contrat le champ « Nombre de jours ouvrés » doit-il être visible ? |  |  |
| Q-0018-02 | 0018 | À poser | La valeur minimale peut-elle être égale à zéro ? |  |  |
| Q-0018-03 | 0018 | À poser | La valeur peut-elle rester vide dans un brouillon, puis devenir obligatoire au lancement ? |  |  |
| Q-0018-04 | 0018 | À poser | Les jours fériés doivent-ils être exclus du calcul des jours ouvrés ? |  |  |
| Q-0018-05 | 0018 | À poser | Une période composée uniquement d'un samedi et d'un dimanche doit-elle être refusée ? |  |  |
| Q-0018-06 | 0018 | À poser | Lorsqu'une date change, la valeur saisie manuellement doit-elle être remplacée par le nouveau calcul ? |  |  |
| Q-0019-01 | 0019 | À poser | Quelles sont les quatre zones qui doivent apparaître dans la vue de consultation d'une DAE ? |  |  |
| Q-0019-02 | 0019 | À poser | La vue de consultation doit-elle reprendre exactement l'ordre et les libellés de la vue d'édition ? |  |  |
| Q-0019-03 | 0019 | À poser | Quels champs doivent rester masqués selon le rôle de l'utilisateur dans la vue de consultation ? |  |  |
| Q-0019-04 | 0019 | À poser | La vue de consultation doit-elle afficher les informations du workflow, les commentaires et l'historique ? |  |  |
| Q-0020-01 | 0020 | À poser | Quels champs doivent être copiés lors de la duplication d'une DAE ? |  |  |
| Q-0020-02 | 0020 | À poser | Quels champs administratifs, de validation ou d'audit doivent impérativement être effacés ? |  |  |
| Q-0020-03 | 0020 | À poser | La nouvelle DAE doit-elle toujours être créée comme brouillon appartenant à l'utilisateur courant ? |  |  |
| Q-0020-04 | 0020 | À poser | Que doit faire l'application lorsqu'une référence copiée est inactive ou supprimée ? |  |  |
| Q-0021-01 | 0021 | À poser | Quelles permissions génériques doivent être disponibles dans l'application ? |  |  |
| Q-0021-02 | 0021 | À poser | Comment les droits du rôle et les droits liés au périmètre doivent-ils se combiner ? |  |  |
| Q-0021-03 | 0021 | À poser | Qui peut administrer l'arbre des départements et les rattachements des utilisateurs ? |  |  |
| Q-0021-04 | 0021 | À poser | Les droits doivent-ils s'appliquer aux départements descendants ou uniquement au département directement rattaché ? |  |  |
| Q-0022-01 | 0022 | À poser | Quelle est la liste officielle des départements à créer, modifier, désactiver ou supprimer ? |  |  |
| Q-0022-02 | 0022 | À poser | Quelle organisation hiérarchique doit être retenue pour les branches SEM et Rédac ? |  |  |
| Q-0022-03 | 0022 | À poser | Comment traiter les départements existants qui ne correspondent plus au référentiel métier ? |  |  |
| Q-0022-04 | 0022 | À poser | Qui valide la nouvelle structure des départements avant son import dans l'application ? |  |  |
| Q-0023-01 | 0023 | À poser | Quelle est l'instance MySQL cible et à quelle date la migration doit-elle être réalisée ? |  |  |
| Q-0023-02 | 0023 | À poser | Quelles données doivent être migrées et quelles données doivent être archivées ou exclues ? |  |  |
| Q-0023-03 | 0023 | À poser | Quelle durée d'indisponibilité est acceptable pendant la migration ? |  |  |
| Q-0023-04 | 0023 | À poser | Quels contrôles métier doivent être réalisés avant d'autoriser la reprise de l'application ? |  |  |
| Q-VAF-0018-01 | validation-applicationform/0018 | À poser | Dans quels cas un cycle de validation peut-il être annulé par un utilisateur ou un administrateur ? |  |  |
| Q-VAF-0018-02 | validation-applicationform/0018 | À poser | Une annulation doit-elle conserver l'historique des votes et des commentaires ? |  |  |
| Q-VAF-0018-03 | validation-applicationform/0018 | À poser | Qui peut réinitialiser un cycle annulé et quelles données doivent être conservées ? |  |  |
| Q-VAF-0019-01 | validation-applicationform/0019 | À poser | Dans quel cas une séquence de validation peut-elle être créée sans rôle configuré ? |  |  |
| Q-VAF-0019-02 | validation-applicationform/0019 | À poser | Qui doit être autorisé à voter lorsqu'une séquence ne possède aucun rôle explicite ? |  |  |
| Q-VAF-0020-01 | validation-applicationform/0020 | À poser | Quels paramètres du workflow doivent être globaux et lesquels doivent être propres à un département ? |  |  |
| Q-VAF-0020-02 | validation-applicationform/0020 | À poser | Une modification du paramétrage doit-elle affecter les cycles déjà lancés ? |  |  |
| Q-VAF-0020-03 | validation-applicationform/0020 | À poser | Qui peut modifier le paramétrage et quelles validations sont nécessaires ? |  |  |
| Q-VAF-0021-01 | validation-applicationform/0021 | À poser | Que doit-il se passer lorsque deux utilisateurs du même rôle votent presque simultanément ? |  |  |
| Q-VAF-0021-02 | validation-applicationform/0021 | À poser | Le second vote doit-il être refusé, remplacé ou conservé comme information d'audit ? |  |  |

## Règles de saisie pendant la réunion

- Ne pas reformuler la réponse du client.
- Reproduire les termes métier employés pendant la réunion.
- Ajouter les exemples concrets dans la colonne dédiée.
- Si la réponse est ambiguë, écrire la réponse telle quelle et conserver le
  statut « À clarifier ».
- Ne pas remplir la colonne « Statut » avec une décision technique.
- Toute interprétation ou proposition de ticket doit être produite dans un
  document séparé après la réunion.
