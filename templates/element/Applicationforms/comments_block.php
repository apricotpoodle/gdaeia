<?php
/**
 * Element : Bloc de rendu de la liste des commentaires
 *
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\Comment>|null $comments
 */
declare(strict_types=1);

$comments = $comments ?? [];
?>

<?php if (!empty($comments)) : ?>
    <div class="d-flex flex-column gap-2" id="comments-wrapper">
        <?php foreach ($comments as $comment) : ?>
            <div class="comment-item p-3 rounded bg-light border-start border-3 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-dark fs-7">
                        <i class="fa-solid fa-circle-user me-1 text-secondary" aria-hidden="true"></i>
                        <?= h($comment->user->display_name ?? $comment->user->username ?? __('Utilisateur')) ?>
                    </strong>
                    <small class="text-muted fs-8">
                        <i class="fa-regular fa-clock me-1" aria-hidden="true"></i>
                        <?= h($comment->created ? $comment->created->format('d/m/Y H:i') : '') ?>
                    </small>
                </div>
                <p class="comment-content text-break mb-1 fs-7">
                    <?= nl2br(h($comment->content)) ?>
                </p>
                <?php if (!empty($comment->type)) : ?>
                    <span class="badge bg-secondary fs-9"><?= h($comment->type) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div id="no-comments-alert" class="text-center text-muted py-4 fs-7">
        <i class="fa-solid fa-inbox me-1" aria-hidden="true"></i>
        <?= __('Aucun commentaire pour le moment.') ?>
    </div>
<?php endif; ?>
