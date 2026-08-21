<?php
/**
 * Element : Zone 5 - Fil de discussion (Offcanvas)
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface $identity
 */
?>

<?php if ($identity->can('viewZoneCommentaires', $applicationform)): ?>
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="offcanvasComments" aria-labelledby="offcanvasCommentsLabel" style="width: 450px;">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title" id="offcanvasCommentsLabel">
                <i class="fa-solid fa-comments me-2"></i><?= __('Fil de discussion') ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <!-- Zone dynamique des messages (injectée via JS) -->
            <div id="comments-container" class="flex-grow-1 p-3 overflow-auto">
                <div class="text-center text-muted py-4" id="comments-loading">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <?= __('Chargement des commentaires...') ?>
                </div>
            </div>

            <!-- Formulaire d'ajout rapide -->
            <?php if ($identity->can('editZoneCommentaires', $applicationform)): ?>
                <div class="p-3 bg-light border-top">
                    <form id="add-comment-form">
                        <div class="mb-2">
                            <textarea id="comment-content" class="form-control" rows="2" placeholder="<?= __('Écrire un commentaire...') ?>" required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <select id="comment-type" class="form-select form-select-sm w-auto">
                                <option value="GENERAL"><?= __('Général') ?></option>
                                <option value="OBSERVATION"><?= __('Observation') ?></option>
                                <option value="HIRING_REASON"><?= __('Motif') ?></option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-paper-plane me-1"></i><?= __('Publier') ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
