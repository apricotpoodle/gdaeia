<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserDepartments Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @method \App\Model\Entity\UserDepartment newEmptyEntity()
 * @method \App\Model\Entity\UserDepartment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserDepartment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserDepartment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserDepartment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserDepartment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserDepartment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserDepartment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserDepartment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserDepartment>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserDepartment> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('department_id')
            ->notEmptyString('department_id');

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
        $rules->add($rules->isUnique(['user_id', 'department_id']), ['errorField' => 'user_id', 'message' => __('This combination of user_id and department_id already exists')]);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }

    /**
     * Custom finder : Récupère la requête des lignes de départements associées à un utilisateur donné.
     * Utilisation : ->find('departmentsOf', user: $userEntity)
     *
     * @param \Cake\ORM\Query\SelectQuery $query L'objet Query de l'ORM.
     * @param \App\Model\Entity\User $user L'entité de l'opérateur.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findDepartmentsOf(SelectQuery $query, User $user): SelectQuery
    {
        return $query->select(['UserDepartments.department_id'])
            ->where(['UserDepartments.user_id' => $user->id]);
    }

    /**
     * Retourne les identifiants des utilisateurs associés à tous les départements fournis.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Requête ORM à spécialiser.
     * @param list<int> $departmentIds Départements sélectionnés et déjà autorisés.
     * @return \Cake\ORM\Query\SelectQuery
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
            ->select(['user_id', 'department_id'])
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
                        'user_id' => $userId,
                        'department_id' => $departmentId,
                    ];
                }
            }
        }

        if ($newAssociations === []) {
            return 0;
        }

        $entities = $this->newEntities($newAssociations);
        if ($this->saveMany($entities, ['atomic' => false]) === false) {
            throw new \RuntimeException('Impossible d\'enregistrer les associations utilisateurs-départements.');
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
                    'user_id' => $userId,
                    'department_id' => $departmentId,
                ];
            }
        }

        if ($newAssociations === []) {
            return 0;
        }

        $entities = $this->newEntities($newAssociations);
        if ($this->saveMany($entities, ['atomic' => false]) === false) {
            throw new \RuntimeException('Impossible de remplacer les associations utilisateurs-départements.');
        }

        return count($entities);
    }
}
