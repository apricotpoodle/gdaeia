<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Contracttype;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Search\Model\Filter\Callback;

/**
 * Applicationforms Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ContracttypesTable> $Contracttypes
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\HiringreasonsTable> $Hiringreasons
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\BudgetfeaturesTable> $Budgetfeatures
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\ProfessionalcategoriesTable> $Professionalcategories
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\WorktimesTable> $Worktimes
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\PeriodsTable> $Periods
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\YesnosTable> $Yesnos
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationformstatusesTable> $Applicationformstatuses
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ApplicationvalidationstepsTable> $Applicationvalidationsteps
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\CurrentvalidationrolesTable> $Currentvalidationroles
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationVisasTable> $ValidationVisas
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\ValidationsTable> $Validations
 * @property \Cake\ORM\Association\HasOne<\App\Model\Table\ValidationWorkflowRunsTable> $ValidationWorkflowRuns
 * @method \App\Model\Entity\Applicationform newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform[] newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Applicationform findOrCreate(\Cake\ORM\Query\SelectQuery<\App\Model\Entity\Applicationform>|callable|array<string, mixed> $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform patchEntity(\App\Model\Entity\Applicationform $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform[] patchEntities(iterable<\App\Model\Entity\Applicationform> $entities, array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform|false save(\App\Model\Entity\Applicationform $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Applicationform saveOrFail(\App\Model\Entity\Applicationform $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationform>|false saveMany(iterable<\App\Model\Entity\Applicationform> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationform> saveManyOrFail(iterable<\App\Model\Entity\Applicationform> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationform>|false deleteMany(iterable<\App\Model\Entity\Applicationform> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<int, \App\Model\Entity\Applicationform> deleteManyOrFail(iterable<\App\Model\Entity\Applicationform> $entities, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\Applicationform>
 * @property \Cake\ORM\Association\HasMany<\App\Model\Table\CommentsTable> $Comments
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @method bool delete(\App\Model\Entity\Applicationform $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\App\Model\Entity\Applicationform $entity, array<string, mixed> $options = [])
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
        $this->setDisplayField(Applicationform::FIELD_JOBTITLE);
        $this->setPrimaryKey(Applicationform::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->addBehavior('Search.Search', [
            'emptyState' => false,
        ]);
        $this->getBehavior('Search')->searchManager()
            ->value(Applicationform::FIELD_DEPARTMENT_ID)
            ->callback('q', [
                'callback' => function (SelectQuery $query, array $args, Callback $filter) {
                    $searchValue = trim((string)($args['q'] ?? ''));
                    if ($searchValue === '') {
                        return true;
                    }

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

                    $query->where($query->expr(
                        'MATCH(Applicationforms.jobtitle, Applicationforms.applicantname, '
                        . 'Applicationforms.qualification, Applicationforms.reasonforreplacement) '
                        . 'AGAINST(:search IN BOOLEAN MODE)',
                    ));
                    $query->bind(':search', $booleanQuery, 'string');

                    return true;
                },
            ]);

        $this->belongsTo('Departments', [
            'foreignKey' => Applicationform::FIELD_DEPARTMENT_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => Applicationform::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Contracttypes', [
            'foreignKey' => Applicationform::FIELD_CONTRACTTYPE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hiringreasons', [
            'foreignKey' => Applicationform::FIELD_HIRINGREASON_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Budgetfeatures', [
            'foreignKey' => Applicationform::FIELD_BUDGETFEATURE_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Professionalcategories', [
            'foreignKey' => Applicationform::FIELD_PROFESSIONALCATEGORY_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Worktimes', [
            'foreignKey' => Applicationform::FIELD_WORKTIME_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Periods', [
            'foreignKey' => Applicationform::FIELD_PERIOD_ID,
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Yesnos', [
            'foreignKey' => Applicationform::FIELD_YESNO_ID,
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
        $this->hasOne('ValidationWorkflowRuns', [
            'foreignKey' => 'applicationform_id',
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'foreign_key',
            'conditions' => ['Comments.model' => 'Applicationforms'],
            'cascadeCallbacks' => true,
            'dependent' => true,
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
            ->integer(Applicationform::FIELD_DEPARTMENT_ID)
            ->notEmptyString(Applicationform::FIELD_DEPARTMENT_ID);

        $validator
            ->integer(Applicationform::FIELD_USER_ID)
            ->notEmptyString(Applicationform::FIELD_USER_ID);

        $validator
            ->scalar(Applicationform::FIELD_CGR)
            ->maxLength(Applicationform::FIELD_CGR, 255)
            ->allowEmptyString(Applicationform::FIELD_CGR);

        $validator
            ->integer(Applicationform::FIELD_CONTRACTTYPE_ID)
            ->notEmptyString(Applicationform::FIELD_CONTRACTTYPE_ID);

        $validator
            ->integer(Applicationform::FIELD_HIRINGREASON_ID)
            ->notEmptyString(Applicationform::FIELD_HIRINGREASON_ID);

        $validator
            ->scalar(Applicationform::FIELD_REASONFORREPLACEMENT)
            ->maxLength(Applicationform::FIELD_REASONFORREPLACEMENT, 255)
            ->allowEmptyString(Applicationform::FIELD_REASONFORREPLACEMENT);

        $validator
            ->integer(Applicationform::FIELD_BUDGETFEATURE_ID)
            ->notEmptyString(Applicationform::FIELD_BUDGETFEATURE_ID);

        $validator
            ->scalar(Applicationform::FIELD_JOBTITLE)
            ->maxLength(Applicationform::FIELD_JOBTITLE, 255)
            ->requirePresence(Applicationform::FIELD_JOBTITLE, 'create')
            ->notEmptyString(Applicationform::FIELD_JOBTITLE);

        $validator
            ->integer(Applicationform::FIELD_PROFESSIONALCATEGORY_ID)
            ->notEmptyString(Applicationform::FIELD_PROFESSIONALCATEGORY_ID);

        $validator
            ->integer(Applicationform::FIELD_WORKTIME_ID)
            ->notEmptyString(Applicationform::FIELD_WORKTIME_ID);

        $validator
            ->scalar(Applicationform::FIELD_WORKINGTIMEDISTRIBUTION)
            ->maxLength(Applicationform::FIELD_WORKINGTIMEDISTRIBUTION, 255)
            ->allowEmptyString(Applicationform::FIELD_WORKINGTIMEDISTRIBUTION);

        $validator
            ->decimal(Applicationform::FIELD_GROSSREMUNERATION)
            ->notEmptyString(Applicationform::FIELD_GROSSREMUNERATION);

        $validator
            ->integer(Applicationform::FIELD_PERIOD_ID)
            ->notEmptyString(Applicationform::FIELD_PERIOD_ID);

        $validator
            ->scalar(Applicationform::FIELD_QUALIFICATION)
            ->maxLength(Applicationform::FIELD_QUALIFICATION, 255)
            ->allowEmptyString(Applicationform::FIELD_QUALIFICATION);

        $validator
            ->date(Applicationform::FIELD_BEGIN_AT)
            ->allowEmptyDate(Applicationform::FIELD_BEGIN_AT);

        $validator
            ->date(Applicationform::FIELD_END_AT)
            ->allowEmptyDate(Applicationform::FIELD_END_AT)
            // Règle 1 : La date de fin doit être supérieure à la date de début
            ->add(Applicationform::FIELD_END_AT, 'greaterThanBegin', [
                'rule' => function ($value, array $context) {
                    if (empty($value) || empty($context['data'][Applicationform::FIELD_BEGIN_AT])) {
                        return true;
                    }

                    return strtotime((string)$value) >= strtotime(
                        (string)$context['data'][Applicationform::FIELD_BEGIN_AT],
                    );
                },
                'message' => __('La date de fin doit être strictement supérieure à la date de début.'),
            ])
            // Règle 2 : Cohérence selon le type de contrat (CDI vs CDD)
            ->add(Applicationform::FIELD_END_AT, 'contractTypeCoherence', [
                'rule' => function ($value, array $context) {
                    $contractTypeId = $context['data'][Applicationform::FIELD_CONTRACTTYPE_ID] ?? null;
                    if (!$contractTypeId) {
                        return true;
                    }

                    // Récupération du type de contrat
                    $contracttypesTable = TableRegistry::getTableLocator()->get('Contracttypes');
                    /** @var \App\Model\Entity\Contracttype|null $contractType */
                    $contractType = $contracttypesTable->find()
                        ->where([Contracttype::FIELD_ID => $contractTypeId])
                        ->first();

                    if (!$contractType) {
                        return true;
                    }

                    $code = strtoupper(trim($contractType->code));

                    // CDI : La date de fin DOIT être nulle
                    if ($code === 'CDI' && !empty($value)) {
                        return __('Un contrat de type CDI ne peut pas comporter de date de fin.');
                    }

                    // CDD : La date de fin est OBLIGATOIRE
                    if (in_array($code, ['CDD', 'CTT', 'ALT']) && empty($value)) {
                        return __('La date de fin est obligatoire pour un contrat à durée déterminée.');
                    }

                    return true;
                },
            ]);

        $validator
            ->scalar(Applicationform::FIELD_APPLICANTNAME)
            ->maxLength(Applicationform::FIELD_APPLICANTNAME, 255)
            ->allowEmptyString(Applicationform::FIELD_APPLICANTNAME);

        $validator
            ->integer(Applicationform::FIELD_YESNO_ID)
            ->notEmptyString(Applicationform::FIELD_YESNO_ID);

        $validator
            ->dateTime(Applicationform::FIELD_DELETED)
            ->allowEmptyDateTime(Applicationform::FIELD_DELETED);

        $validator
            ->nonNegativeInteger(Applicationform::FIELD_COLLABORATOR_ID)
            ->allowEmptyString(Applicationform::FIELD_COLLABORATOR_ID);

        $validator
            ->dateTime(Applicationform::FIELD_ARCHIVED)
            ->allowEmptyDateTime(Applicationform::FIELD_ARCHIVED);

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
        $rules->add($rules->existsIn([Applicationform::FIELD_DEPARTMENT_ID], 'Departments'), [
            'errorField' => Applicationform::FIELD_DEPARTMENT_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_USER_ID], 'Users'), [
            'errorField' => Applicationform::FIELD_USER_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_CONTRACTTYPE_ID], 'Contracttypes'), [
            'errorField' => Applicationform::FIELD_CONTRACTTYPE_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_HIRINGREASON_ID], 'Hiringreasons'), [
            'errorField' => Applicationform::FIELD_HIRINGREASON_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_BUDGETFEATURE_ID], 'Budgetfeatures'), [
            'errorField' => Applicationform::FIELD_BUDGETFEATURE_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_PROFESSIONALCATEGORY_ID], 'Professionalcategories'), [
            'errorField' => Applicationform::FIELD_PROFESSIONALCATEGORY_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_WORKTIME_ID], 'Worktimes'), [
            'errorField' => Applicationform::FIELD_WORKTIME_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_PERIOD_ID], 'Periods'), [
            'errorField' => Applicationform::FIELD_PERIOD_ID,
        ]);
        $rules->add($rules->existsIn([Applicationform::FIELD_YESNO_ID], 'Yesnos'), [
            'errorField' => Applicationform::FIELD_YESNO_ID,
        ]);

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
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query L'objet Query de l'ORM.
     * @param \App\Model\Entity\User $user L'opérateur courant.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        // 1. Le Super Admin a une vision globale (pas de filtre)
        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return $query;
        }

        // 2. Récupération du périmètre des départements de l'utilisateur
        // On s'appuie sur la table de liaison UserDepartments
        $userDepartmentsTable = TableRegistry::getTableLocator()->get('UserDepartments');
        $myDepartmentIds = $userDepartmentsTable->find('departmentsOf', user: $user);

        // 3. Application du filtre strict (Créateur OU Appartient à mon département)
        return $query->where([
            'OR' => [
                'Applicationforms.user_id' => $user->id,
                'Applicationforms.department_id IN' => $myDepartmentIds,
            ],
        ]);
    }

    /**
     * Crée une nouvelle DAE indépendante à partir d'une DAE existante.
     *
     * Les commentaires, les visas et le cycle de validation ne sont pas
     * copiés : ils sont liés à l'enregistrement source et la nouvelle demande
     * doit repartir en brouillon avec son nouveau demandeur.
     *
     * @param \App\Model\Entity\Applicationform $source DAE à recopier.
     * @param \App\Model\Entity\User $actor Utilisateur qui déclenche la copie.
     * @return \App\Model\Entity\Applicationform|false Nouvelle DAE ou échec de validation/persistance.
     */
    public function duplicateFor(Applicationform $source, User $actor): Applicationform|false
    {
        $fields = [
            Applicationform::FIELD_DEPARTMENT_ID,
            Applicationform::FIELD_CGR,
            Applicationform::FIELD_CONTRACTTYPE_ID,
            Applicationform::FIELD_HIRINGREASON_ID,
            Applicationform::FIELD_REASONFORREPLACEMENT,
            Applicationform::FIELD_BUDGETFEATURE_ID,
            Applicationform::FIELD_JOBTITLE,
            Applicationform::FIELD_PROFESSIONALCATEGORY_ID,
            Applicationform::FIELD_WORKTIME_ID,
            Applicationform::FIELD_WORKINGTIMEDISTRIBUTION,
            Applicationform::FIELD_GROSSREMUNERATION,
            Applicationform::FIELD_PERIOD_ID,
            Applicationform::FIELD_QUALIFICATION,
            Applicationform::FIELD_BEGIN_AT,
            Applicationform::FIELD_END_AT,
            Applicationform::FIELD_APPLICANTNAME,
            Applicationform::FIELD_YESNO_ID,
            Applicationform::FIELD_COLLABORATOR_ID,
        ];
        $data = $source->extract($fields);
        $data[Applicationform::FIELD_USER_ID] = $actor->get(User::FIELD_ID);

        $duplicate = $this->newEntity($data);

        return $this->save($duplicate) ? $duplicate : false;
    }
}
