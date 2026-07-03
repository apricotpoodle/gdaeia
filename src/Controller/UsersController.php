<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Class UsersController (Web)
 *
 * Contrôleur d'interface utilisateur. 
 * A pour unique responsabilité de livrer les vues HTML au navigateur.
 * La récupération des données est déléguée à l'API en AJAX.
 *
 * @package App\Controller
 */
class UsersController extends AppController
{
    /**
     * Méthode Index (GET /users)
     * * Rend le gabarit HTML contenant le conteneur vide pour la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        // Le rendu de templates/Users/index.php est automatique.
    }
}