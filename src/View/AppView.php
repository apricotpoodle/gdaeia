<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\View;

use Cake\View\View;


/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 * @extends \Cake\View\View<\App\View\AppView>
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        // 💡 Résolution automatique par CakePHP du Helper d'autorisation
        // $this->loadHelper('Identity', [
        //     'className' => 'Authorization.Identity',
        // ]);        // // 1. Chargement du Helper d'Autorisation (Fournit $this->Identity dans les templates)
        // $this->addHelper('Authorization.Identity');
        // 2. Chargement global du Helper d'infrastructure
        $this->loadHelper('Tabulator');
        // 3. Injection de l'objet $identity dans toutes les vues (.php)
        $identity = $this->getRequest()->getAttribute('identity');
        $this->set('identity', $identity);

    }
}
