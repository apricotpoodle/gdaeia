<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Catalogue complet des permissions retenues pour les tests. */
class PermissionsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'resource' => 'departments', 'action' => 'read', 'label' => 'Consulter les départements'],
            ['id' => 2, 'resource' => 'departments', 'action' => 'manage', 'label' => 'Administrer les départements'],
            ['id' => 3, 'resource' => 'menus', 'action' => 'manage', 'label' => 'Administrer les menus'],
            ['id' => 4, 'resource' => 'applicationforms', 'action' => 'create', 'label' => 'Créer une demande'],
            ['id' => 5, 'resource' => 'applicationforms', 'action' => 'read', 'label' => 'Consulter les demandes'],
            ['id' => 6, 'resource' => 'applicationforms', 'action' => 'update', 'label' => 'Modifier les demandes'],
            ['id' => 7, 'resource' => 'applicationforms', 'action' => 'delete', 'label' => 'Supprimer les demandes'],
            ['id' => 8, 'resource' => 'applicationforms', 'action' => 'vote', 'label' => 'Voter sur les demandes'],
            ['id' => 9, 'resource' => 'users', 'action' => 'read', 'label' => 'Consulter les utilisateurs'],
            ['id' => 10, 'resource' => 'users', 'action' => 'update', 'label' => 'Modifier les utilisateurs'],
            ['id' => 11, 'resource' => 'validationsequences', 'action' => 'read', 'label' => 'Consulter les séquences de validation'],
            ['id' => 12, 'resource' => 'users', 'action' => 'create', 'label' => 'Créer les utilisateurs'],
            ['id' => 13, 'resource' => 'users', 'action' => 'delete', 'label' => 'Supprimer les utilisateurs'],
            ['id' => 14, 'resource' => 'users', 'action' => 'change_role', 'label' => 'Changer les rôles des utilisateurs'],
            ['id' => 15, 'resource' => 'users', 'action' => 'associate_departments', 'label' => 'Associer les départements des utilisateurs'],
            ['id' => 16, 'resource' => 'users', 'action' => 'impersonate', 'label' => 'Usurper une identité'],
            ['id' => 17, 'resource' => 'applicationforms', 'action' => 'launch_validation', 'label' => 'Lancer la validation des demandes'],
            ['id' => 18, 'resource' => 'applicationforms', 'action' => 'reset_validation', 'label' => 'Réinitialiser la validation des demandes'],
            ['id' => 19, 'resource' => 'applicationforms', 'action' => 'remind_validation', 'label' => 'Relancer la validation des demandes'],
            ['id' => 20, 'resource' => 'applicationforms', 'action' => 'override_validation', 'label' => 'Outrepasser la validation des demandes'],
            ['id' => 21, 'resource' => 'applicationforms', 'action' => 'archive', 'label' => 'Archiver les demandes'],
            ['id' => 22, 'resource' => 'references', 'action' => 'read', 'label' => 'Consulter les références'],
            ['id' => 23, 'resource' => 'references', 'action' => 'create', 'label' => 'Créer les références'],
            ['id' => 24, 'resource' => 'references', 'action' => 'update', 'label' => 'Modifier les références'],
            ['id' => 25, 'resource' => 'references', 'action' => 'delete', 'label' => 'Supprimer les références'],
            ['id' => 26, 'resource' => 'roles', 'action' => 'read', 'label' => 'Consulter les rôles'],
            ['id' => 27, 'resource' => 'roles', 'action' => 'create', 'label' => 'Créer les rôles'],
            ['id' => 28, 'resource' => 'roles', 'action' => 'update', 'label' => 'Modifier les rôles'],
            ['id' => 29, 'resource' => 'roles', 'action' => 'delete', 'label' => 'Supprimer les rôles'],
            ['id' => 30, 'resource' => 'fieldauthorizations', 'action' => 'read', 'label' => 'Consulter les autorisations de champs'],
            ['id' => 31, 'resource' => 'fieldauthorizations', 'action' => 'create', 'label' => 'Créer les autorisations de champs'],
            ['id' => 32, 'resource' => 'fieldauthorizations', 'action' => 'update', 'label' => 'Modifier les autorisations de champs'],
            ['id' => 33, 'resource' => 'fieldauthorizations', 'action' => 'delete', 'label' => 'Supprimer les autorisations de champs'],
            ['id' => 34, 'resource' => 'workflowsettings', 'action' => 'read', 'label' => 'Consulter le paramétrage global du workflow'],
            ['id' => 35, 'resource' => 'workflowsettings', 'action' => 'update', 'label' => 'Modifier le paramétrage global du workflow'],
            ['id' => 36, 'resource' => 'validationcommenttemplates', 'action' => 'read', 'label' => 'Consulter les commentaires prédéfinis'],
            ['id' => 37, 'resource' => 'validationcommenttemplates', 'action' => 'create', 'label' => 'Créer les commentaires prédéfinis'],
            ['id' => 38, 'resource' => 'validationcommenttemplates', 'action' => 'update', 'label' => 'Modifier les commentaires prédéfinis'],
            ['id' => 39, 'resource' => 'validationcommenttemplates', 'action' => 'delete', 'label' => 'Supprimer les commentaires prédéfinis'],
            ['id' => 40, 'resource' => 'validationsequences', 'action' => 'create', 'label' => 'Créer les séquences de validation'],
            ['id' => 41, 'resource' => 'validationsequences', 'action' => 'update', 'label' => 'Modifier les séquences de validation'],
            ['id' => 42, 'resource' => 'validationsequences', 'action' => 'delete', 'label' => 'Supprimer les séquences de validation'],
        ];
        parent::init();
    }
}
