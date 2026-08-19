<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Class ApplicationformsController (Web)
 *
 * Contrôleur d'interface utilisateur pour la page d'entrée.
 *
 * Ce contrôleur (Skinny Controller) ne sert qu'à livrer la vue et la structure HTML.
 */
class ApplicationformsController extends AppController
{
    /**
     * Méthode Index (GET /applicationforms ou /)
     * Rend le gabarit HTML contenant le conteneur vide pour la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        // Application de la politique d'autorisation (Nécessite la création future d'ApplicationformPolicy)
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'index');
    }
}
