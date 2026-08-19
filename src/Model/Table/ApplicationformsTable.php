<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Search\Manager;

/**
 * Applicationforms Model
 *
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ContracttypesTable&\Cake\ORM\Association\BelongsTo $Contracttypes
 * @property \App\Model\Table\HiringreasonsTable&\Cake\ORM\Association\BelongsTo $Hiringreasons
 * @property \App\Model\Table\BudgetfeaturesTable&\Cake\ORM\Association\BelongsTo $Budgetfeatures
 * @property \App\Model\Table\ProfessionalcategoriesTable&\Cake\ORM\Association\BelongsTo $Professionalcategories
 * @property \App\Model\Table\WorktimesTable&\Cake\ORM\Association\BelongsTo $Worktimes
 * @property \App\Model\Table\PeriodsTable&\Cake\ORM\Association\BelongsTo $Periods
 * @property \App\Model\Table\YesnosTable&\Cake\ORM\Association\BelongsTo $Yesnos
 * @property \App\Model\Table\ApplicationformstatusesTable&\Cake\ORM\Association\HasMany $Applicationformstatuses
 * @property \App\Model\Table\ApplicationvalidationstepsTable&\Cake\ORM\Association\HasMany $Applicationvalidationsteps
 * @property \App\Model\Table\CurrentvalidationrolesTable&\Cake\ORM\Association\HasMany $Currentvalidationroles
 * @property \App\Model\Table\ValidationVisasTable&\Cake\ORM\Association\HasMany $ValidationVisas
 * @property \App\Model\Table\ValidationsTable&\Cake\ORM\Association\HasMany $Validations
 *
 * @method \App\Model\Entity\Applicationform newEmptyEntity()
 * @method \App\Model\Entity\Applicationform newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationform> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Applicationform get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationform findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Applicationform patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Applicationform> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Applicationform|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Applicationform saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationform>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationform>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationform>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationform> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationform>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationform>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Applicationform>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Applicationform> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ApplicationformsTable extends Table
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

        $this->setTable('applicationforms');
        $this->setDisplayField('jobtitle');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Search.Search', [
            'emptyState' => false,
        ]);

        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Contracttypes', [
            'foreignKey' => 'contracttype_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hiringreasons', [
            'foreignKey' => 'hiringreason_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Budgetfeatures', [
            'foreignKey' => 'budgetfeature_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Professionalcategories', [
            'foreignKey' => 'professionalcategory_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Worktimes', [
            'foreignKey' => 'worktime_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Periods', [
            'foreignKey' => 'period_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Yesnos', [
            'foreignKey' => 'yesno_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Applicationformstatuses', [
            'foreignKey' => 'applicationform_id',
        ]);
        $this->hasMany('Applicationvalidationsteps', [
            'foreignKey' => 'applicationform_id',
        ]);
        $this->hasMany('Currentvalidationroles', [
            'foreignKey' => 'applicationform_id',
        ]);
        $this->hasMany('ValidationVisas', [
            'foreignKey' => 'applicationform_id',
        ]);
        $this->hasMany('Validations', [
            'foreignKey' => 'applicationform_id',
        ]);
    }

    /**
     * Configuration des filtres de recherche pour FriendsOfCake/Search
     *
     * @return \Search\Manager
     */
    public function searchManager(): Manager
    {
        $searchManager = $this->behaviors()->get('Search')->searchManager();

        // Filtre exact par département
        $searchManager->value('department_id');

        // 🚀 FILTRE CALLBACK FULLTEXT PERFORMANCE (MATCH AGAINST IN BOOLEAN MODE)
        $searchManager->callback('q', [
            'callback' => function (SelectQuery $query, array $args, \Search\Model\Filter\Callback $filter) {
                $searchValue = trim((string)($args['q'] ?? ''));
                if ($searchValue === '') {
                    return true;
                }

                // Découpage et normalisation des termes pour MySQL BOOLEAN MODE (+mot*)
                $terms = array_filter(explode(' ', $searchValue));
                $booleanQuery = '';
                foreach ($terms as $term) {
                    $term = trim($term);
                    if ($term !== '') {
                        $booleanQuery .= '+' . $term . '* ';
                    }
                }

                $booleanQuery = trim($booleanQuery);
                if ($booleanQuery === '') {
                    return true;
                }

                // Application de la recherche FULLTEXT multi-colonnes
                $query->where([
                    'MATCH(Applicationforms.jobtitle, Applicationforms.applicantname, Applicationforms.qualification, Applicationforms.reasonforreplacement) AGAINST(:search IN BOOLEAN MODE)' => [
                        'search' => $booleanQuery,
                    ],
                ]);

                return true;
            },
        ]);

        return $searchManager;
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
            ->integer('department_id')
            ->notEmptyString('department_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('cgr')
            ->maxLength('cgr', 255)
            ->allowEmptyString('cgr');

        $validator
            ->integer('contracttype_id')
            ->notEmptyString('contracttype_id');

        $validator
            ->integer('hiringreason_id')
            ->notEmptyString('hiringreason_id');

        $validator
            ->scalar('reasonforreplacement')
            ->maxLength('reasonforreplacement', 255)
            ->allowEmptyString('reasonforreplacement');

        $validator
            ->integer('budgetfeature_id')
            ->notEmptyString('budgetfeature_id');

        $validator
            ->scalar('jobtitle')
            ->maxLength('jobtitle', 255)
            ->requirePresence('jobtitle', 'create')
            ->notEmptyString('jobtitle');

        $validator
            ->integer('professionalcategory_id')
            ->notEmptyString('professionalcategory_id');

        $validator
            ->integer('worktime_id')
            ->notEmptyString('worktime_id');

        $validator
            ->scalar('workingtimedistribution')
            ->maxLength('workingtimedistribution', 255)
            ->allowEmptyString('workingtimedistribution');

        $validator
            ->decimal('grossremuneration')
            ->notEmptyString('grossremuneration');

        $validator
            ->integer('period_id')
            ->notEmptyString('period_id');

        $validator
            ->scalar('qualification')
            ->maxLength('qualification', 255)
            ->allowEmptyString('qualification');

        $validator
            ->date('begin_at')
            ->allowEmptyDate('begin_at');

        $validator
            ->date('end_at')
            ->allowEmptyDate('end_at');

        $validator
            ->scalar('applicantname')
            ->maxLength('applicantname', 255)
            ->allowEmptyString('applicantname');

        $validator
            ->integer('yesno_id')
            ->notEmptyString('yesno_id');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['contracttype_id'], 'Contracttypes'), ['errorField' => 'contracttype_id']);
        $rules->add($rules->existsIn(['hiringreason_id'], 'Hiringreasons'), ['errorField' => 'hiringreason_id']);
        $rules->add($rules->existsIn(['budgetfeature_id'], 'Budgetfeatures'), ['errorField' => 'budgetfeature_id']);
        $rules->add($rules->existsIn(['professionalcategory_id'], 'Professionalcategories'), ['errorField' => 'professionalcategory_id']);
        $rules->add($rules->existsIn(['worktime_id'], 'Worktimes'), ['errorField' => 'worktime_id']);
        $rules->add($rules->existsIn(['period_id'], 'Periods'), ['errorField' => 'period_id']);
        $rules->add($rules->existsIn(['yesno_id'], 'Yesnos'), ['errorField' => 'yesno_id']);

        return $rules;
    }

    /**
     * Custom finder : Restreint la liste des demandes à celles visibles par l'opérateur.
     * - Les Super Admins voient tout.
     * - Les autres ne voient que les demandes liées à un département qu'ils gèrent/observent,
     *   ou les demandes dont ils sont les créateurs.
     *
     * Utilisation : ->find('visibleTo', user: $currentUser)
     *
     * @param \Cake\ORM\Query\SelectQuery $query L'objet Query de l'ORM.
     * @param \App\Model\Entity\User $user L'opérateur courant.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findVisibleTo(SelectQuery $query, \App\Model\Entity\User $user): SelectQuery
    {
        // 1. Le Super Admin a une vision globale (pas de filtre)
        if ($user->get('issuperuser')) {
            return $query;
        }

        // 2. Récupération du périmètre des départements de l'utilisateur
        // On s'appuie sur la table de liaison UserDepartments
        $userDepartmentsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('UserDepartments');
        $myDepartmentIds = $userDepartmentsTable->find('departmentsOf', user: $user);

        // 3. Application du filtre strict (Créateur OU Appartient à mon département)
        return $query->where([
            'OR' => [
                'Applicationforms.user_id' => $user->id,
                'Applicationforms.department_id IN' => $myDepartmentIds
            ]
        ]);
    }

}
