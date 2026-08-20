<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Comments Model
 */
class CommentsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('comments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ParentComments', [
            'className' => 'Comments',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildComments', [
            'className' => 'Comments',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->scalar('model')
            ->maxLength('model', 64)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->nonNegativeInteger('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmptyString('foreign_key');

        $validator
            ->scalar('type')
            ->maxLength('type', 32)
            ->notEmptyString('type');

        $validator
            ->scalar('content')
            ->requirePresence('content', 'create')
            ->notEmptyString('content');

        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentComments'), ['errorField' => 'parent_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Custom finder : Restreint la liste des commentaires selon le périmètre de l'opérateur.
     *
     * @param SelectQuery $query
     * @param User $user
     * @return SelectQuery
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        $query = parent::findVisibleTo($query, $user);

        if ($user->get('issuperuser')) {
            return $query;
        }

        // Sécurité : Filtrage optionnel par auteur ou vérification du périmètre
        // Exemple : Les utilisateurs ne voient que les commentaires non supprimés
        // et reliés aux demandes auxquelles ils ont accès.
        return $query;
    }

}
