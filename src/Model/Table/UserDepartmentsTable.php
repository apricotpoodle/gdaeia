<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use App\Model\Entity\UserDepartment;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException;

/**
 * UserDepartments Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @method \App\Model\Entity\UserDepartment newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserDepartment findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\UserDepartment>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment patchEntity(\App\Model\Entity\UserDepartment $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment[] patchEntities(iterable<\App\Model\Entity\UserDepartment> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment|false save(\App\Model\Entity\UserDepartment $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\UserDepartment saveOrFail(\App\Model\Entity\UserDepartment $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\UserDepartment>|false saveMany(iterable<\App\Model\Entity\UserDepartment> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\UserDepartment> saveManyOrFail(iterable<\App\Model\Entity\UserDepartment> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\UserDepartment>|false deleteMany(iterable<\App\Model\Entity\UserDepartment> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\UserDepartment> deleteManyOrFail(iterable<\App\Model\Entity\UserDepartment> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\UserDepartment>
 * @method bool delete(\App\Model\Entity\UserDepartment $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\UserDepartment $entity, array<string, mixed> $options = [])
 */
class UserDepartmentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_departments');
        $this->setDisplayField(UserDepartment::FIELD_ID);
        $this->setPrimaryKey(UserDepartment::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => UserDepartment::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => UserDepartment::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer(UserDepartment::FIELD_USER_ID)
            ->notEmptyString(UserDepartment::FIELD_USER_ID);

        $validator
            ->integer(UserDepartment::FIELD_DEPARTMENT_ID)
            ->notEmptyString(UserDepartment::FIELD_DEPARTMENT_ID);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique([UserDepartment::FIELD_USER_ID, UserDepartment::FIELD_DEPARTMENT_ID]), [
            'errorField' => UserDepartment::FIELD_USER_ID,
            'message' => __('Cet utilisateur est déjà associé à ce département.'),
        ]);
        $rules->add($rules->existsIn([UserDepartment::FIELD_USER_ID], 'Users'), [
            'errorField' => UserDepartment::FIELD_USER_ID,
        ]);
        $rules->add($rules->existsIn([UserDepartment::FIELD_DEPARTMENT_ID], 'Departments'), [
            'errorField' => UserDepartment::FIELD_DEPARTMENT_ID,
        ]);

        return $rules;
    }

    /**
     * Custom finder : Récupère la requête des lignes de départements associées à un utilisateur donné.
     * Utilisation : ->find('departmentsOf', user: $userEntity)
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query L'objet Query de l'ORM.
     * @param \App\Model\Entity\User $user L'entité de l'opérateur.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findDepartmentsOf(SelectQuery $query, User $user): SelectQuery
    {
        return $query->select(['UserDepartments.department_id'])
            ->where(['UserDepartments.user_id' => $user->id]);
    }

    /**
     * Retourne les identifiants des utilisateurs associés à tous les départements fournis.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query Requête ORM à spécialiser.
     * @param list<int> $departmentIds Départements sélectionnés et déjà autorisés.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findUserIdsAssociatedWithDepartments(SelectQuery $query, array $departmentIds): SelectQuery
    {
        return $query->select(['UserDepartments.user_id'])
            ->where(['UserDepartments.department_id IN' => $departmentIds])
            ->groupBy(['UserDepartments.user_id'])
            ->having(['COUNT(DISTINCT UserDepartments.department_id) =' => count($departmentIds)]);
    }

    /**
     * Retire les associations ciblées entre un utilisateur et des départements.
     *
     * @param int $userId Identifiant de l'utilisateur déjà autorisé.
     * @param list<int> $departmentIds Identifiants des départements déjà autorisés.
     * @return int Nombre d'associations retirées.
     */
    public function removeAssociationsForUser(int $userId, array $departmentIds): int
    {
        return $this->deleteAll([
            'UserDepartments.user_id' => $userId,
            'UserDepartments.department_id IN' => $departmentIds,
        ]);
    }

    /**
     * Ajoute les associations absentes entre plusieurs utilisateurs et départements.
     *
     * Les associations existantes sont conservées : cette opération représente un ajout
     * de droits explicites, et non le remplacement d'un périmètre utilisateur.
     *
     * @param list<int> $userIds Identifiants des utilisateurs déjà autorisés.
     * @param list<int> $departmentIds Identifiants des départements déjà autorisés.
     * @return int Nombre d'associations effectivement créées.
     * @throws \RuntimeException Si une association ne peut pas être sauvegardée.
     */
    public function addMissingAssociations(array $userIds, array $departmentIds): int
    {
        $existingAssociations = $this->find()
            ->select([UserDepartment::FIELD_USER_ID, UserDepartment::FIELD_DEPARTMENT_ID])
            ->where([
                'UserDepartments.user_id IN' => $userIds,
                'UserDepartments.department_id IN' => $departmentIds,
            ])
            ->all();

        $existingKeys = [];
        foreach ($existingAssociations as $association) {
            $existingKeys[(int)$association->user_id . ':' . (int)$association->department_id] = true;
        }

        $newAssociations = [];
        foreach ($userIds as $userId) {
            foreach ($departmentIds as $departmentId) {
                $key = $userId . ':' . $departmentId;
                if (!isset($existingKeys[$key])) {
                    $newAssociations[] = [
                        UserDepartment::FIELD_USER_ID => $userId,
                        UserDepartment::FIELD_DEPARTMENT_ID => $departmentId,
                    ];
                }
            }
        }

        if ($newAssociations === []) {
            return 0;
        }

        $entities = $this->newEntities($newAssociations);
        if ($this->saveMany($entities, ['atomic' => false]) === false) {
            throw new RuntimeException('Impossible d\'enregistrer les associations utilisateurs-départements.');
        }

        return count($entities);
    }

    /**
     * Remplace intégralement les périmètres explicites des utilisateurs ciblés.
     *
     * Cette méthode doit être appelée dans une transaction par le service appelant :
     * si une nouvelle association est invalide, les suppressions sont annulées.
     *
     * @param list<int> $userIds Identifiants des utilisateurs déjà autorisés.
     * @param list<int> $departmentIds Identifiants des départements déjà autorisés.
     * @return int Nombre d'associations créées après le remplacement.
     * @throws \RuntimeException Si une association ne peut pas être sauvegardée.
     */
    public function replaceAssociationsForUsers(array $userIds, array $departmentIds): int
    {
        $this->deleteAll(['UserDepartments.user_id IN' => $userIds]);

        $newAssociations = [];
        foreach ($userIds as $userId) {
            foreach ($departmentIds as $departmentId) {
                $newAssociations[] = [
                    UserDepartment::FIELD_USER_ID => $userId,
                    UserDepartment::FIELD_DEPARTMENT_ID => $departmentId,
                ];
            }
        }

        if ($newAssociations === []) {
            return 0;
        }

        $entities = $this->newEntities($newAssociations);
        if ($this->saveMany($entities, ['atomic' => false]) === false) {
            throw new RuntimeException('Impossible de remplacer les associations utilisateurs-départements.');
        }

        return count($entities);
    }
}
