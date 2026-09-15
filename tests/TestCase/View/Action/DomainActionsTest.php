<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Action;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\View\Action\ApplicationformsActions;
use App\View\Action\FieldAuthorizationsActions;
use App\View\Action\MenusActions;
use App\View\Action\PublicActions;
use App\View\Action\RolesActions;
use App\View\Action\UiAction;
use App\View\Action\UsersActions;
use Cake\TestSuite\TestCase;

/** Vérifie le contrat déclaratif des commandes rendues par ActionHelper. */
class DomainActionsTest extends TestCase
{
    /** Vérifie le contrat d'autorisation et de navigation des actions Menus. */
    public function testLesActionsMenusPortentLeurPolicyEtLeurRessource(): void
    {
        $action = MenusActions::roleAccess();

        $this->assertSame('roleAccess', $action->authorizationAction);
        $this->assertSame('Menus', $action->resource);
        $this->assertSame(['action' => 'roleAccess'], $action->url);
    }

    /** Vérifie qu'une action contextuelle Users cible son entité. */
    public function testLesActionsUtilisateursContextuellesPortentLEntiteCible(): void
    {
        $user = new User(['id' => 42]);
        $action = UsersActions::edit($user);

        $this->assertSame('edit', $action->authorizationAction);
        $this->assertSame($user, $action->resource);
        $this->assertSame(['action' => 'edit', 42], $action->url);
    }

    /** Vérifie qu'une suppression est rendue comme une action HTTP POST protégée. */
    public function testLaSuppressionDeDemandeEstUneCommandePostProtegee(): void
    {
        $applicationform = new Applicationform(['id' => 12]);
        $action = ApplicationformsActions::delete($applicationform);

        $this->assertSame(UiAction::TYPE_POST_LINK, $action->type);
        $this->assertSame('delete', $action->authorizationAction);
        $this->assertSame($applicationform, $action->resource);
    }

    /** Vérifie qu'une consultation Applicationforms cible son entité. */
    public function testLaConsultationDeDemandePorteLEntiteCible(): void
    {
        $applicationform = new Applicationform(['id' => 12]);
        $action = ApplicationformsActions::view($applicationform);

        $this->assertSame('view', $action->authorizationAction);
        $this->assertSame($applicationform, $action->resource);
        $this->assertSame(['action' => 'view', 12], $action->url);
    }

    /** Vérifie que les retours restent soumis à la Policy de consultation de liste. */
    public function testLesActionsDeRetourDesDomainesRestentSoumisesALaPolicyIndex(): void
    {
        $this->assertSame('index', MenusActions::index()->authorizationAction);
        $this->assertSame('index', UsersActions::index()->authorizationAction);
        $this->assertSame('index', ApplicationformsActions::index()->authorizationAction);
        $this->assertSame('index', FieldAuthorizationsActions::index()->authorizationAction);
        $this->assertSame('index', RolesActions::index()->authorizationAction);
    }

    /** Vérifie le contrat de la commande de création des rôles. */
    public function testLaCreationDeRoleCibleLeDomaineRoles(): void
    {
        $action = RolesActions::add();

        $this->assertSame('add', $action->authorizationAction);
        $this->assertSame('Roles', $action->resource);
        $this->assertSame(['action' => 'add'], $action->url);
    }

    /** Vérifie que le raccourci des associations délègue son autorisation à Menus. */
    public function testLAssociationDesRolesAuxMenusCibleLEcranDeMenus(): void
    {
        $action = RolesActions::menuAccess();

        $this->assertSame('roleAccess', $action->authorizationAction);
        $this->assertSame('Menus', $action->resource);
        $this->assertSame(['controller' => 'Menus', 'action' => 'roleAccess'], $action->url);
    }

    /** Vérifie que les actions publiques contournent explicitement les Policies. */
    public function testLesActionsPubliquesNeDemandentPasDAutorisation(): void
    {
        $this->assertFalse(PublicActions::home()->requiresAuthorization);
        $this->assertFalse(PublicActions::forgotPassword()->requiresAuthorization);
        $this->assertFalse(PublicActions::login()->requiresAuthorization);
    }
}
