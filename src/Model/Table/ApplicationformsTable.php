<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Contracttype;
use App\Model\Entity\Period;
use App\Model\Entity\User;
use App\Service\ApplicationformDuplicationResult;
use App\Service\SalaryCalculationService;
use App\Service\ValidationErrorPresenter;
use App\Service\WorkingDaysService;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\Log;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Search\Model\Filter\Callback;
use Throwable;

/**
 * Applicationforms Model
 *
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\DepartmentsTable> $Departments
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Users
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Assistants
 * @property \Cake\ORM\Association\BelongsTo<\App\Model\Table\UsersTable> $Managers
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
    /** Champs administratifs toujours effacés lors d'une duplication. */
    public const DUPLICATION_RESET_FIELDS = [
        Applicationform::FIELD_USER_ID,
        Applicationform::FIELD_ASSISTANT_ID,
        Applicationform::FIELD_MANAGER_ID,
        Applicationform::FIELD_APPLICANTNAME,
        Applicationform::FIELD_APPLICANT_FIRST_NAME,
        Applicationform::FIELD_APPLICANT_LAST_NAME,
        Applicationform::FIELD_COLLABORATOR_ID,
        Applicationform::FIELD_SALARY_BASE,
        Applicationform::FIELD_HR_DESCRIPTION,
        Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION,
        Applicationform::FIELD_FINAL_ARGUMENT,
        Applicationform::FIELD_BUDGETFEATURE_ID,
        Applicationform::FIELD_YESNO_ID,
        Applicationform::FIELD_QUALIFICATION,
        Applicationform::FIELD_ARCHIVED,
        Applicationform::FIELD_DELETED,
    ];

    /** Champs scalaires pouvant provenir de la DAE source avant réinitialisation. */
    private const DUPLICATION_SOURCE_FIELDS = [
        Applicationform::FIELD_DEPARTMENT_ID,
        Applicationform::FIELD_ASSISTANT_ID,
        Applicationform::FIELD_MANAGER_ID,
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
        Applicationform::FIELD_APPLICANT_FIRST_NAME,
        Applicationform::FIELD_APPLICANT_LAST_NAME,
        Applicationform::FIELD_WORKING_DAYS,
        Applicationform::FIELD_SALARY_BASE,
        Applicationform::FIELD_HR_DESCRIPTION,
        Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION,
        Applicationform::FIELD_FINAL_ARGUMENT,
        Applicationform::FIELD_YESNO_ID,
        Applicationform::FIELD_COLLABORATOR_ID,
        Applicationform::FIELD_ARCHIVED,
        Applicationform::FIELD_DELETED,
    ];

    /** Références à contrôler contre les référentiels actifs. */
    private const DUPLICATION_REFERENCES = [
        Applicationform::FIELD_DEPARTMENT_ID => ['Departments', 'Département'],
        Applicationform::FIELD_CONTRACTTYPE_ID => ['Contracttypes', 'Type de contrat'],
        Applicationform::FIELD_HIRINGREASON_ID => ['Hiringreasons', 'Motif de recrutement'],
        Applicationform::FIELD_PROFESSIONALCATEGORY_ID => ['Professionalcategories', 'Catégorie professionnelle'],
        Applicationform::FIELD_WORKTIME_ID => ['Worktimes', 'Temps de travail'],
        Applicationform::FIELD_PERIOD_ID => ['Periods', 'Période'],
    ];

    /**
     * Normalise les saisies décimales avant le marshalling CakePHP.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Événement ORM.
     * @param \ArrayObject<string, mixed> $data Données reçues.
     * @param \ArrayObject<string, mixed> $options Options de marshalling.
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach ([Applicationform::FIELD_ASSISTANT_ID, Applicationform::FIELD_MANAGER_ID] as $field) {
            if (!$data->offsetExists($field)) {
                continue;
            }

            $value = $data[$field];
            if ($value === 0 || (is_string($value) && trim($value) === '0')) {
                $data[$field] = null;
            }
        }

        foreach (
            [
            Applicationform::FIELD_GROSSREMUNERATION,
            Applicationform::FIELD_SALARY_BASE,
            ] as $field
        ) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = str_replace(',', '.', trim($data[$field]));
            }
        }
    }

    /**
     * Synchronise les données dérivées et calcule le salaire avant persistance.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Événement ORM.
     * @param \Cake\Datasource\EntityInterface $entity Entité sauvegardée.
     * @param \ArrayObject<string, mixed> $options Options de sauvegarde.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$entity instanceof Applicationform) {
            return;
        }

        if ($this->preventCircuitChange($entity)) {
            $event->stopPropagation();
            $event->setResult(false);

            return;
        }

        $entity->synchronizeApplicantName();
        $periodId = $entity->get(Applicationform::FIELD_PERIOD_ID);
        if ($periodId === null || $entity->get(Applicationform::FIELD_GROSSREMUNERATION) === null) {
            return;
        }

        $period = $this->Periods->get($periodId);
        $periodCode = strtoupper(trim((string)$period->get(Period::FIELD_CODE)));
        if (!in_array($periodCode, [...Period::CODES,], true)) {
            return;
        }

        try {
            $workingDaysValue = $entity->get(Applicationform::FIELD_WORKING_DAYS);
            $workingDays = $workingDaysValue === null || $workingDaysValue === ''
                ? null
                : (int)$workingDaysValue;
            $entity->set(
                Applicationform::FIELD_SALARY_BASE,
                (new SalaryCalculationService())->calculateForPeriodCode(
                    $periodCode,
                    $entity->get(Applicationform::FIELD_GROSSREMUNERATION),
                    $workingDays,
                    $this->configuredKCoefficient(),
                ),
            );
        } catch (InvalidArgumentException $exception) {
            $entity->setError(Applicationform::FIELD_SALARY_BASE, ['calculation' => $exception->getMessage()]);
            $event->setResult(false);

            return;
        }
    }

    /** Détermine si l'instantané d'un cycle verrouille le contrat et le département. */
    public function isCircuitLocked(Applicationform $applicationform): bool
    {
        return !$applicationform->isNew() && $this->ValidationWorkflowRuns->exists([
            'applicationform_id' => $applicationform->id,
        ]);
    }

    /** Empêche tout changement du circuit, y compris lorsque checkRules est désactivé. */
    private function preventCircuitChange(Applicationform $applicationform): bool
    {
        $fields = [Applicationform::FIELD_DEPARTMENT_ID, Applicationform::FIELD_CONTRACTTYPE_ID];
        if (
            (!$applicationform->isDirty($fields[0]) && !$applicationform->isDirty($fields[1]))
            || !$this->isCircuitLocked($applicationform)
        ) {
            return false;
        }

        $persisted = $this->find()->select($fields)
            ->where(['Applicationforms.id' => $applicationform->id])->firstOrFail();
        $blocked = false;
        foreach ($fields as $field) {
            if ((int)$applicationform->get($field) !== (int)$persisted->get($field)) {
                $applicationform->setError($field, [
                    'circuitLocked' => __(
                        'Le contrat et le département sont verrouillés tant qu’un cycle de validation existe.',
                    ),
                ]);
                $blocked = true;
            }
        }

        return $blocked;
    }

    /** Retourne le coefficient k administré dans les réglages du workflow. */
    private function configuredKCoefficient(): float
    {
        $setting = TableRegistry::getTableLocator()->get('WorkflowSettings')->find()
            ->where(['name' => 'salary.k_coefficient'])->first();
        $value = $setting?->get('value');

        return is_numeric($value) && (float)$value > 0
            ? (float)$value
            : SalaryCalculationService::DEFAULT_K_COEFFICIENT;
    }

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
        $this->belongsTo('Assistants', [
            'className' => 'Users',
            'foreignKey' => Applicationform::FIELD_ASSISTANT_ID,
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Managers', [
            'className' => 'Users',
            'foreignKey' => Applicationform::FIELD_MANAGER_ID,
            'joinType' => 'LEFT',
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

        foreach ([Applicationform::FIELD_ASSISTANT_ID, Applicationform::FIELD_MANAGER_ID] as $field) {
            $validator->nonNegativeInteger($field)->allowEmptyString($field);
        }

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
            ->allowEmptyString(Applicationform::FIELD_BUDGETFEATURE_ID);

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

        foreach ([Applicationform::FIELD_APPLICANT_FIRST_NAME, Applicationform::FIELD_APPLICANT_LAST_NAME] as $field) {
            $validator->scalar($field)->maxLength($field, 120)->allowEmptyString($field);
        }

        $validator
            ->integer(Applicationform::FIELD_WORKING_DAYS)
            ->greaterThanOrEqual(Applicationform::FIELD_WORKING_DAYS, 0)
            ->add(Applicationform::FIELD_WORKING_DAYS, 'withinDateRange', [
                'rule' => function ($value, array $context): bool|string {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    $begin = $this->validationDate($context['data'][Applicationform::FIELD_BEGIN_AT] ?? null);
                    $end = $this->validationDate($context['data'][Applicationform::FIELD_END_AT] ?? null);
                    if ($begin === null || $end === null || $end < $begin) {
                        return true;
                    }

                    $maximum = (new WorkingDaysService())->countCalendarDays($begin, $end);

                    return (int)$value <= $maximum
                        ?: __('Le nombre de jours travaillés ne peut pas dépasser la durée de la période.');
                },
            ])
            ->allowEmptyString(Applicationform::FIELD_WORKING_DAYS);

        $validator
            ->decimal(Applicationform::FIELD_SALARY_BASE)
            ->allowEmptyString(Applicationform::FIELD_SALARY_BASE);

        foreach (
            [
                Applicationform::FIELD_HR_DESCRIPTION,
                Applicationform::FIELD_CONTROL_MANAGEMENT_VALIDATION,
                Applicationform::FIELD_FINAL_ARGUMENT,
            ] as $field
        ) {
            $validator->scalar($field)->allowEmptyString($field);
        }

        $validator
            ->integer(Applicationform::FIELD_YESNO_ID)
            ->allowEmptyString(Applicationform::FIELD_YESNO_ID);

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

    /** Convertit une date de validation en date civile exploitable. */
    private function validationDate(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)->setTime(0, 0);
        }

        if (is_object($value) && method_exists($value, 'format')) {
            $value = $value->format('Y-m-d');
        }
        if (!is_string($value) || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10));

        return $date !== false && $date->format('Y-m-d') === substr($value, 0, 10) ? $date : null;
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
        foreach (
            [
                Applicationform::FIELD_ASSISTANT_ID => 'Assistants',
                Applicationform::FIELD_MANAGER_ID => 'Managers',
            ] as $field => $association
        ) {
            $rules->add($rules->existsIn([$field], $association), ['errorField' => $field]);
        }
        $rules->add(
            function (Applicationform $applicationform): bool {
                return $this->assistantMatchesDepartment($applicationform);
            },
            [
                'errorField' => Applicationform::FIELD_ASSISTANT_ID,
                'message' => __(
                    'L’assistante doit être active, avoir le rôle Demandeur et appartenir à ce département.',
                ),
            ],
        );
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
     * Vérifie que l'assistante sélectionnée figure dans les options visibles de l'opérateur.
     *
     * Cette vérification complète les règles d'intégrité qui ne disposent pas
     * de l'identité courante. Les mutations Web et API l'appellent après
     * l'hydratation et avant la sauvegarde.
     *
     * @param \App\Model\Entity\Applicationform $applicationform DAE à vérifier.
     * @param \App\Model\Entity\User $operator Opérateur courant.
     * @return void
     */
    public function validateAssistantVisibility(Applicationform $applicationform, User $operator): void
    {
        $assistantId = $applicationform->get(Applicationform::FIELD_ASSISTANT_ID);
        if ($assistantId === null || $assistantId === '' || $assistantId === 0 || $assistantId === '0') {
            return;
        }

        $assistantId = filter_var($assistantId, FILTER_VALIDATE_INT);
        $departmentId = filter_var(
            $applicationform->get(Applicationform::FIELD_DEPARTMENT_ID),
            FILTER_VALIDATE_INT,
        );
        if ($assistantId === false || $assistantId < 1 || $departmentId === false || $departmentId < 1) {
            return;
        }

        if (!$this->assistantMatchesDepartment($applicationform)) {
            return;
        }

        $isVisible = $this->Users
            ->find('assistantsForDepartment', departmentId: $departmentId)
            ->find('visibleTo', user: $operator)
            ->where(['Users.id' => $assistantId])
            ->count() > 0;

        if (!$isVisible) {
            $applicationform->setError(Applicationform::FIELD_ASSISTANT_ID, [
                'visibility' => __('L’assistante ne figure pas parmi les options visibles pour ce département.'),
            ], false);
        }
    }

    /**
     * Vérifie que l'assistante choisie appartient au département de la demande.
     *
     * @param \App\Model\Entity\Applicationform $applicationform Demande à vérifier.
     * @return bool Vrai sans assistante, ou si elle est éligible.
     */
    private function assistantMatchesDepartment(Applicationform $applicationform): bool
    {
        $assistantId = $applicationform->get(Applicationform::FIELD_ASSISTANT_ID);
        if ($assistantId === null || $assistantId === '' || (int)$assistantId === 0) {
            return true;
        }

        $departmentId = (int)$applicationform->get(Applicationform::FIELD_DEPARTMENT_ID);
        if ($departmentId < 1) {
            return false;
        }

        /** @var \App\Model\Table\UsersTable $users */
        $users = TableRegistry::getTableLocator()->get('Users');

        return $users->find('assistantsForDepartment', departmentId: $departmentId)
            ->where(['Users.id' => (int)$assistantId])
            ->count() > 0;
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
     * @return \App\Service\ApplicationformDuplicationResult Résultat de la duplication.
     */
    public function duplicateFor(Applicationform $source, User $actor): ApplicationformDuplicationResult
    {
        $fields = array_values(array_diff(self::DUPLICATION_SOURCE_FIELDS, self::DUPLICATION_RESET_FIELDS));
        $data = $source->extract($fields);
        $referenceFailure = $this->duplicateReferenceFailure($data);
        if ($referenceFailure !== null) {
            return $referenceFailure;
        }
        $data[Applicationform::FIELD_USER_ID] = $actor->get(User::FIELD_ID);

        $duplicate = $this->newEntity($data);
        try {
            if ($this->save($duplicate)) {
                return ApplicationformDuplicationResult::success($duplicate);
            }
        } catch (Throwable $exception) {
            Log::error('Échec technique lors de la duplication d’une DAE.', [
                'exception' => $exception,
                'source_id' => $source->get(Applicationform::FIELD_ID),
            ]);

            return ApplicationformDuplicationResult::failure(
                ApplicationformDuplicationResult::CATEGORY_PERSISTENCE,
                __('La duplication est impossible à enregistrer. Vérifiez les données puis réessayez.'),
            );
        }

        if ($duplicate->getErrors() !== []) {
            $presentation = (new ValidationErrorPresenter())->present($duplicate, 'Applicationforms');

            return ApplicationformDuplicationResult::failure(
                ApplicationformDuplicationResult::CATEGORY_VALIDATION,
                __('La duplication est bloquée. Corrigez les champs signalés puis recommencez.'),
                $presentation['errors'],
            );
        }

        return ApplicationformDuplicationResult::failure(
            ApplicationformDuplicationResult::CATEGORY_PERSISTENCE,
            __('La duplication est impossible à enregistrer. Vérifiez les données puis réessayez.'),
        );
    }

    /**
     * Vérifie que les références de la copie pointent vers des référentiels actifs.
     *
     * @param array<string, mixed> $data Données métier extraites de la source.
     * @return \App\Service\ApplicationformDuplicationResult|null Échec ou null si toutes les références sont valides.
     */
    private function duplicateReferenceFailure(array $data): ?ApplicationformDuplicationResult
    {
        foreach (self::DUPLICATION_REFERENCES as $field => [$tableName, $label]) {
            $value = $data[$field] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $table = TableRegistry::getTableLocator()->get($tableName);
            $query = $table->find()->where(['id' => (int)$value]);
            if ($table->getSchema()->hasColumn('deleted')) {
                $query->where(['deleted IS' => null]);
            }
            if ($query->first() !== null) {
                continue;
            }

            $message = __(
                'La référence « {0} » est invalide ou supprimée. Sélectionnez de nouveau une valeur active.',
                $label,
            );

            return ApplicationformDuplicationResult::failure(
                ApplicationformDuplicationResult::CATEGORY_REFERENCE,
                $message,
                [[
                    'field' => $field,
                    'label' => $label,
                    'reason' => __('Sélectionnez de nouveau une valeur active.'),
                ]],
            );
        }

        return null;
    }
}
