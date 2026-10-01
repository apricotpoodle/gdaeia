<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\CommentsController
 */
class CommentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Comments',
        'app.Users',
    ];

    public function testLApiDesCommentairesRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/comments.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesCommentairesRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/comments.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    public function testUnOperateurNePeutPasSupprimerLeCommentaireDAutrui(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();
        $this->delete('/api/comments/1.json');

        $this->assertResponseCode(403);
        $this->assertResponseContains('"success":false');
    }

    public function testUnOperateurNePeutPasModifierLeCommentaireDAutrui(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();

        $this->post('/api/comments/edit/1.json', ['content' => 'Modification refusée']);

        $this->assertResponseCode(403);
        $this->assertResponseContains('"success":false');
        $this->assertSame('Commentaire protege', $this->getTableLocator()->get('Comments')->get(1)->content);
    }

    public function testLApiPresenteLesErreursDeValidationDuCommentaire(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/comments/add.json', ['model' => 'Applicationforms', 'foreign_key' => 1, 'content' => '']);

        $this->assertResponseCode(422);
        $this->assertResponseContains('Champ « Contenu » :');
        $this->assertResponseContains('"field":"content","label":"Contenu","reason":');
    }

    public function testLaModificationApiRetourneLeMessageEtLeChampDuCommentaire(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/comments/edit/1.json', ['content' => '']);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"message":"Champ « Contenu » :');
        $this->assertResponseContains('"field":"content","label":"Contenu","reason":');
    }
}
