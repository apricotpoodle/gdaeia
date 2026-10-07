<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AppController;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;

/** Expose les méthodes protégées pour le test du socle de contrôleurs. */
final class TestableAppController extends AppController
{
    public function flashErrors(EntityInterface $entity, string $resource): void
    {
        $this->flashValidationErrors($entity, $resource);
    }

    public function validationErrors(EntityInterface $entity, string $resource): Response
    {
        return $this->validationErrorResponse($entity, $resource);
    }

    /** @return callable */
    public function rightsFormatter(array $extraActions = [], ?callable $columnsFormatter = null): callable
    {
        return $this->createGridRightsFormatter($extraActions, $columnsFormatter);
    }
}
