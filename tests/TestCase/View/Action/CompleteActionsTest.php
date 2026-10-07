<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Action;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\View\Action\ApplicationformsActions;
use App\View\Action\FieldAuthorizationsActions;
use App\View\Action\MenusActions;
use App\View\Action\PublicActions;
use App\View\Action\ReferencesActions;
use App\View\Action\RolesActions;
use App\View\Action\UiAction;
use App\View\Action\UsersActions;
use App\View\Action\ValidationsequencesActions;
use App\View\Action\WorkflowSettingsActions;
use Cake\TestSuite\TestCase;

/** Exécute toutes les fabriques d’actions et leurs variantes optionnelles. */
class CompleteActionsTest extends TestCase
{
    public function testToutesLesFabriquesRetourneUneActionDeclarative(): void
    {
        $applicationform = new Applicationform(['id' => 12]);
        $role = new Role(['id' => 7]);
        $user = new User(['id' => 42, 'display_name' => 'Valérie Test']);

        $actions = [
            ApplicationformsActions::add(),
            ApplicationformsActions::edit($applicationform),
            ApplicationformsActions::edit($applicationform, true),
            ApplicationformsActions::view($applicationform),
            ApplicationformsActions::viewPdf($applicationform),
            ApplicationformsActions::duplicate($applicationform),
            ApplicationformsActions::delete($applicationform),
            ApplicationformsActions::delete($applicationform, true),
            ApplicationformsActions::launchValidation($applicationform),
            ApplicationformsActions::resetValidation($applicationform),
            ApplicationformsActions::index('Demandes', 'btn-test'),
            FieldAuthorizationsActions::add(),
            FieldAuthorizationsActions::index('Autorisations', 'btn-test'),
            MenusActions::roleAccess(),
            MenusActions::add(),
            MenusActions::index('Menus'),
            PublicActions::home(),
            PublicActions::forgotPassword(),
            PublicActions::login(),
            ReferencesActions::add('Contracttypes'),
            ReferencesActions::index('Contracttypes', 'Références'),
            RolesActions::add(),
            RolesActions::menuAccess(),
            RolesActions::index('Rôles', 'btn-test'),
            RolesActions::edit($role),
            UsersActions::bulkDepartments(),
            UsersActions::edit($user),
            UsersActions::impersonate($user),
            UsersActions::index('Utilisateurs', 'btn-test'),
            ValidationsequencesActions::workflowSettings(),
            WorkflowSettingsActions::validationSequences(),
            WorkflowSettingsActions::saveDefaultDueHours(),
            WorkflowSettingsActions::saveCommentRequirements(),
            WorkflowSettingsActions::saveCommentTemplate(),
        ];

        $this->assertCount(34, $actions);
        $this->assertContainsOnlyInstancesOf(UiAction::class, $actions);
        $this->assertFalse(PublicActions::home()->requiresAuthorization);
        $this->assertSame('btn-test', ApplicationformsActions::index(class: 'btn-test')->options['class']);
    }
}
