<?php
declare(strict_types=1);

namespace App\Test\TestCase\View;

use App\View\AjaxView;
use App\View\AppView;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/** Vérifie l’initialisation des vues applicatives et AJAX. */
class AppViewTest extends TestCase
{
    public function testLaVueApplicativeChargeLesHelpersCommunsEtExposeLIdentite(): void
    {
        $request = new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']);
        $view = new AppView($request);

        $this->assertTrue($view->helpers()->has('Tabulator'));
        $this->assertTrue($view->helpers()->has('Action'));
        $this->assertTrue($view->helpers()->has('FieldMetadata'));
        $this->assertNull($view->get('identity'));
    }

    public function testLaVueAjaxUtiliseLeTypeDeReponseAjax(): void
    {
        $view = new AjaxView(new ServerRequest(['base' => '', 'url' => '/', 'webroot' => '/']));

        $this->assertSame('text/html', $view->getResponse()->getType());
        $this->assertTrue($view->helpers()->has('Action'));
    }
}
