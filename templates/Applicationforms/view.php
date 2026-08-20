<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface|null $identity
 */
?>
<div class="row">
    <!-- Panel d'actions contextuelles (Conforme ADR 0046) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= h($applicationform->jobtitle) ?> <small class="text-muted">(#<?= h($applicationform->id) ?>)</small></h2>

        <div class="d-flex gap-2">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i> ' . __('Retour à la liste'),
                ['action' => 'index'],
                ['escape' => false, 'class' => 'btn btn-outline-secondary btn-sm']
            ) ?>

            <?php if ($identity?->can('edit', $applicationform)): ?>
                <?= $this->Html->link(
                    '<i class="fas fa-edit me-1"></i> ' . __('Modifier'),
                    ['action' => 'edit', $applicationform->id],
                    ['escape' => false, 'class' => 'btn btn-outline-primary btn-sm']
                ) ?>
            <?php endif; ?>

            <?php if ($identity?->can('delete', $applicationform)): ?>
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash me-1"></i> ' . __('Supprimer'),
                    ['action' => 'delete', $applicationform->id],
                    [
                        'escape' => false,
                        'confirm' => __('Êtes-vous sûr de vouloir supprimer la demande #{0} ?', $applicationform->id),
                        'class' => 'btn btn-outline-danger btn-sm'
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="column-responsive column-80">
        <div class="applicationforms view content">
            <h3><?= h($applicationform->jobtitle) ?> (#<?= h($applicationform->id) ?>)</h3>
            
            <?php if ($applicationform->archived): ?>
                <div class="alert alert-warning">
                    <?= __('Cette fiche a été archivée le {0}.', h($applicationform->archived->format('d/m/Y H:i'))) ?>
                </div>
            <?php endif; ?>

            <table>
                <tr>
                    <th><?= __('Intitulé du poste') ?></th>
                    <td><?= h($applicationform->jobtitle) ?></td>
                </tr>
                <tr>
                    <th><?= __('Département / Service') ?></th>
                    <td><?= $applicationform->has('department') ? h($applicationform->department->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Demandeur') ?></th>
                    <td><?= $applicationform->has('user') ? h($applicationform->user->email) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Type de contrat') ?></th>
                    <td><?= $applicationform->has('contracttype') ? h($applicationform->contracttype->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Motif d\'embauche') ?></th>
                    <td><?= $applicationform->has('hiringreason') ? h($applicationform->hiringreason->name) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Collaborateur concerné (ID)') ?></th>
                    <td><?= $applicationform->collaborator_id ? h($applicationform->collaborator_id) : 'N/A' ?></td>
                </tr>
                <tr>
                    <th><?= __('Rémunération brute') ?></th>
                    <td><?= $this->Number->currency($applicationform->grossremuneration, 'EUR') ?></td>
                </tr>
                <tr>
                    <th><?= __('Dates de contrat') ?></th>
                    <td>
                        <?= $applicationform->begin_at ? h($applicationform->begin_at->format('d/m/Y')) : '-' ?>
                        <?= $applicationform->end_at ? ' au ' . h($applicationform->end_at->format('d/m/Y')) : '' ?>
                    </td>
                </tr>
            </table>

            <hr />

            <div class="comments-section">
                <h4><?= __('Observations et Fil de discussion') ?></h4>

                <div id="comments-list">
                    <?php if (!empty($applicationform->comments)): ?>
                        <?php foreach ($applicationform->comments as $comment): ?>
                            <div class="comment-item" style="margin-left: <?= ($comment->parent_id ? '30px' : '0') ?>; border-left: 2px solid #ccc; padding-left: 10px; margin-bottom: 10px;">
                                <p style="margin-bottom: 0;">
                                    <strong><?= h($comment->user ? $comment->user->email : 'Anonyme') ?></strong>
                                    <small>(<?= h($comment->type) ?>) - <?= h($comment->created->format('d/m/Y H:i')) ?></small>
                                </p>
                                <div><?= nl2br(h($comment->content)) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><em><?= __('Aucune observation ou commentaire pour le moment.') ?></em></p>
                    <?php endif; ?>
                </div>

                <div class="add-comment-box" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                    <h5><?= __('Ajouter une observation / un message') ?></h5>
                    <form id="form-add-comment" data-csrf-token="<?= h($this->request->getAttribute('csrfToken')) ?>">
                        <input type="hidden" name="model" value="Applicationforms" />
                        <input type="hidden" name="foreign_key" value="<?= $applicationform->id ?>" />
                        
                        <div class="input select">
                            <label for="comment-type"><?= __('Contexte') ?></label>
                            <select name="type" id="comment-type">
                                <option value="GENERAL"><?= __('Général / Remarque') ?></option>
                                <option value="OBSERVATION"><?= __('Observation') ?></option>
                                <option value="HIRING_REASON"><?= __('Précision Motif Embauche') ?></option>
                                <option value="PART_TIME"><?= __('Précision Temps Partiel') ?></option>
                            </select>
                        </div>

                        <div class="input textarea">
                            <textarea name="content" id="comment-content" rows="3" required placeholder="<?= __('Saisissez votre remarque...') ?>"></textarea>
                        </div>

                        <button type="submit" class="button"><?= __('Envoyer l\'observation') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Injection propre du JS selon ADR 0046 -->
<?= $this->Html->script('views/applicationforms/comments-handler', ['block' => true]) ?>