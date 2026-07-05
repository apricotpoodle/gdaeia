# ADR 0028 : Infrastructure d'Authentification Évolutive et Habilitations de Session

**Statut :** Proposé

## Contexte
L'application requiert la mise en place d'un système d'authentification et d'autorisation robuste, capable de gérer des accès par couple identifiant/mot de passe dans un premier temps, tout en garantissant une extension transparente vers un fournisseur d'identité tiers (Google OAuth 2.0) à moyen terme. De plus, une fonctionnalité d'usurpation d'identité (`impersonate`) est requise pour le support technique de niveau Super Administrateur.

## Décisions
1. **Couple Authentication / Authorization** : Adoption des deux plugins officiels de CakePHP pour découpler l'identification (Middleware) du contrôle d'accès métier (Policies).
2. **Persistance Évolutive (Google Ready)** : Schématisation de la table `users` intégrant nativement un champ `google_id` nullable. L'authentification future par Google réutilisera le même flux d'identification en ajoutant simplement un `GoogleAuthenticator` au gestionnaire sans altérer l'arborescence des droits.
3. **Cinématique de Confiance** : Verrouillage des comptes à l'inscription via un drapeau `is_verified` soumis à la validation d'un jeton à usage unique transmis par canal SMTP (Mailer).
4. **Encapsulation de l'Impersonate** : Isolation de l'état de session d'origine du Super Administrateur lors du switch d'identité, adossée à un contrôle strict par la Policy `UsersPolicy::canImpersonate`.

## Conséquences
* L'ensemble des contrôleurs de l'application exigera par défaut une identité valide, sauf dérogation explicite (`$this->Authentication->addUnauthenticatedActions()`).
