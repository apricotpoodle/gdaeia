<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\Constraint\Response\StatusCode;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * PagesControllerTest class
 *
 * @link \App\Controller\PagesController
 */
class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testLaPageAccueilRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/pages/home');
        $this->assertRedirectContains('/users/login');
    }

    public function testUnePageInconnueNeContournePasLaConnexion(): void
    {
        $this->get('/pages/not_existing');
        $this->assertRedirectContains('/users/login');
    }

    public function testLeModeDebugNeContournePasLaConnexion(): void
    {
        Configure::write('debug', true);
        $this->get('/pages/not_existing');
        $this->assertRedirectContains('/users/login');
    }

    public function testUnCheminSuspectNeContournePasLaConnexion(): void
    {
        $this->get('/pages/../Layout/ajax');
        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test that CSRF protection is applied to page rendering.
     *
     * @return void
     */
    public function testLaProtectionCsrfRefuseUnPostSansJeton(): void
    {
        $this->post('/pages/home', ['hello' => 'world']);

        $this->assertResponseCode(403);
        $this->assertResponseContains('CSRF');
    }

    /**
     * Test that CSRF protection is applied to page rendering.
     *
     * @return void
     */
    public function testLaProtectionCsrfAccepteUnPostAvecJeton(): void
    {
        $this->enableCsrfToken();
        $this->post('/pages/home', ['hello' => 'world']);

        $this->assertThat(403, $this->logicalNot(new StatusCode($this->_response)));
        $this->assertResponseNotContains('CSRF');
    }
}
