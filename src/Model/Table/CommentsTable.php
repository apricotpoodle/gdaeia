<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Comment;
use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Comments Model
 */
class CommentsTable extends AppTable
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('comments');
        $this->setDisplayField(Comment::FIELD_ID);
        $this->setPrimaryKey(Comment::FIELD_ID);

        $this->addBehavior('Timestamp');

        $this->belongsTo('ParentComments', [
            'className' => 'Comments',
            'foreignKey' => Comment::FIELD_PARENT_ID,
        ]);
        $this->hasMany('ChildComments', [
            'className' => 'Comments',
            'foreignKey' => Comment::FIELD_PARENT_ID,
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => Comment::FIELD_USER_ID,
            'joinType' => 'INNER',
        ]);
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger(Comment::FIELD_PARENT_ID)
            ->allowEmptyString(Comment::FIELD_PARENT_ID);

        $validator
            ->scalar(Comment::FIELD_MODEL)
            ->maxLength(Comment::FIELD_MODEL, 64)
            ->requirePresence(Comment::FIELD_MODEL, 'create')
            ->notEmptyString(Comment::FIELD_MODEL);

        $validator
            ->nonNegativeInteger(Comment::FIELD_FOREIGN_KEY)
            ->requirePresence(Comment::FIELD_FOREIGN_KEY, 'create')
            ->notEmptyString(Comment::FIELD_FOREIGN_KEY);

        $validator
            ->scalar(Comment::FIELD_TYPE)
            ->maxLength(Comment::FIELD_TYPE, 32)
            ->notEmptyString(Comment::FIELD_TYPE);

        $validator
            ->scalar(Comment::FIELD_CONTENT)
            ->requirePresence(Comment::FIELD_CONTENT, 'create')
            ->notEmptyString(Comment::FIELD_CONTENT);

        $validator
            ->nonNegativeInteger(Comment::FIELD_USER_ID)
            ->notEmptyString(Comment::FIELD_USER_ID);

        return $validator;
    }

    /** @inheritDoc */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn([Comment::FIELD_PARENT_ID], 'ParentComments'), [
            'errorField' => Comment::FIELD_PARENT_ID,
        ]);
        $rules->add($rules->existsIn([Comment::FIELD_USER_ID], 'Users'), ['errorField' => Comment::FIELD_USER_ID]);

        return $rules;
    }

    /**
     * Custom finder : Restreint la liste des commentaires selon le périmètre de l'opérateur.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        $query = parent::findVisibleTo($query, $user);

        if ($user->get(User::FIELD_ISSUPERUSER)) {
            return $query;
        }

        // Sécurité : Filtrage optionnel par auteur ou vérification du périmètre
        // Exemple : Les utilisateurs ne voient que les commentaires non supprimés
        // et reliés aux demandes auxquelles ils ont accès.
        return $query;
    }
}
