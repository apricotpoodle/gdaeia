<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Action;

use App\View\Action\UiAction;
use App\View\Action\ValidationCircuitRulesActions;
use App\View\Action\ValidationsequencesActions;
use Cake\TestSuite\TestCase;

/** Vérifie les frontières d’autorisation des commandes de circuits locaux. */
class ValidationCircuitRulesActionsTest extends TestCase
{
    /** Les deux points d’entrée portent la Policy du nouveau domaine. */
    public function testLesLiensDuDomaineUtilisentLeDroitDeConsultationLocal(): void
    {
        $actions = [
            ValidationCircuitRulesActions::index(),
            ValidationCircuitRulesActions::navigation(),
            ValidationsequencesActions::circuitRules(),
        ];
        foreach ($actions as $action) {
            $this->assertSame(UiAction::TYPE_LINK, $action->type);
            $this->assertSame('index', $action->authorizationAction);
            $this->assertSame('ValidationCircuitRules', $action->resource);
            $this->assertSame(['controller' => 'ValidationCircuitRules', 'action' => 'index'], $action->url);
        }
    }

    /** Les commandes de formulaire restent soumises au droit de gestion local. */
    public function testLesCommandesDeReglesSontProtegeesParLaGestionLocale(): void
    {
        $actions = [
            ValidationCircuitRulesActions::saveRule(),
            ValidationCircuitRulesActions::preview(),
            ValidationCircuitRulesActions::newRule(),
        ];
        foreach ($actions as $action) {
            $this->assertSame(UiAction::TYPE_BUTTON, $action->type);
            $this->assertSame('manage', $action->authorizationAction);
            $this->assertSame('ValidationCircuitRules', $action->resource);
            $this->assertTrue($action->requiresAuthorization);
        }
    }

    /** L’attribution des habilitations demande le droit distinct des super-administrateurs. */
    public function testLAttributionDesHabilitationsDemandeLeDroitSpecifique(): void
    {
        $action = ValidationCircuitRulesActions::assignManagers();

        $this->assertSame(UiAction::TYPE_BUTTON, $action->type);
        $this->assertSame('manageGrants', $action->authorizationAction);
        $this->assertSame('ValidationCircuitRules', $action->resource);
        $this->assertTrue($action->requiresAuthorization);
    }
}
