<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ValidationErrorPresenter;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

/** Vérifie la présentation des erreurs ORM pour les interfaces Web et API. */
class ValidationErrorPresenterTest extends TestCase
{
    public function testPresenteToutesLesErreursAvecLeursLibellesDansLOrdreOrm(): void
    {
        $entity = new Entity();
        $entity->setErrors([
            'department_id' => ['_empty' => 'Ce champ est obligatoire.'],
            'jobtitle' => [
                '_empty' => 'Ce champ est obligatoire.',
                'maxLength' => 'La valeur est trop longue.',
            ],
        ]);

        $this->assertSame([
            'summary' => 'Champ « Département » : Ce champ est obligatoire.',
            'errors' => [
                ['field' => 'department_id', 'label' => 'Département', 'reason' => 'Ce champ est obligatoire.'],
                ['field' => 'jobtitle', 'label' => 'Intitulé du poste', 'reason' => 'Ce champ est obligatoire.'],
                ['field' => 'jobtitle', 'label' => 'Intitulé du poste', 'reason' => 'La valeur est trop longue.'],
            ],
        ], (new ValidationErrorPresenter())->present($entity, 'Applicationforms'));
    }

    public function testConserveLeCheminDesAssociationsImbriquees(): void
    {
        $entity = new Entity();
        $entity->setErrors([
            'user_departments' => [
                0 => ['department_id' => ['_required' => 'Le département est obligatoire.']],
                1 => ['department_id' => ['exists' => 'Le département est introuvable.']],
            ],
        ]);

        $this->assertSame([
            'summary' => 'Champ « Département » : Le département est obligatoire.',
            'errors' => [
                [
                    'field' => 'user_departments.0.department_id',
                    'label' => 'Département',
                    'reason' => 'Le département est obligatoire.',
                ],
                [
                    'field' => 'user_departments.1.department_id',
                    'label' => 'Département',
                    'reason' => 'Le département est introuvable.',
                ],
            ],
        ], (new ValidationErrorPresenter())->present($entity, 'Users'));
    }

    public function testUnChampInconnuResteIdentifiable(): void
    {
        $entity = new Entity();
        $entity->setErrors(['champ_inconnu' => ['_empty' => 'Ce champ est obligatoire.']]);

        $this->assertSame([
            'summary' => 'Champ « Champ non répertorié (champ_inconnu) » : Ce champ est obligatoire.',
            'errors' => [[
                'field' => 'champ_inconnu',
                'label' => 'Champ non répertorié (champ_inconnu)',
                'reason' => 'Ce champ est obligatoire.',
            ]],
        ], (new ValidationErrorPresenter())->present($entity, 'Applicationforms'));
    }

    public function testUneEntiteSansErreurRetourneUnResultatVide(): void
    {
        $this->assertSame([
            'summary' => 'Aucun détail de validation n’a été retourné.',
            'errors' => [],
        ], (new ValidationErrorPresenter())->present(new Entity(), 'Users'));
    }
}
