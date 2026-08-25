<?php
/**
 * Element : Zone de gestion des commentaires (Volet latéral Offcanvas)
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface $identity
 */
declare(strict_types=1);
?>

<!-- Offcanvas Zone Commentaires -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasComments" aria-labelledby="offcanvasCommentsLabel">
    <div class="offcanvas-header bg-light border-bottom flex-shrink-0 d-flex justify-content-between align-items-center">
        <h5 class="offcanvas-title d-flex align-items-center mb-0" id="offcanvasCommentsLabel">
            <i class="fa-solid fa-comments text-primary me-2" aria-hidden="true"></i>
            <?= __('Commentaires') ?>
        </h5>

        <div class="d-flex align-items-center gap-2">
            <!-- Bouton discret de bascule du tri (Anti-chronologique par défaut) -->
            <button type="button"
                    id="btn-toggle-sort"
                    class="btn btn-sm btn-outline-secondary py-0 px-2 fs-8"
                    data-order="desc"
                    title="<?= __('Inverser l\'ordre d\'affichage (Plus récents / Plus anciens)') ?>">
                <i class="fa-solid fa-arrow-down-wide-short me-1" id="sort-icon" aria-hidden="true"></i>
                <span id="sort-label"><?= __('Plus récents') ?></span>
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= __('Fermer') ?>"></button>
        </div>
    </div>

    <!-- Conteneur flexbox occupant 100% de la hauteur interne -->
    <div class="offcanvas-body d-flex flex-column p-3 overflow-hidden">

        <!-- Zone déroulante des commentaires (Défilement scrollable interne) -->
        <div id="comments-container" class="comments-scroll-container flex-grow-1 overflow-y-auto pe-2 mb-3" style="max-height: calc(100vh - 280px); min-height: 0;">
            <!-- Rendu PHP initial -->
            <?= $this->element('Applicationforms/comments_block', [
                'comments' => $applicationform->comments ?? [],
            ]) ?>
        </div>

        <!-- Zone de saisie d'un nouveau commentaire ancrée en bas -->
        <?php if ($identity->can('editZoneCommentaires', $applicationform)) : ?>
            <div class="comment-input-block border-top pt-3 flex-shrink-0 bg-white mt-auto">
                <form id="add-comment-form" method="post" action="#">
                    <div class="mb-2">
                        <label for="comment-type" class="form-label fw-semibold fs-7 mb-1">
                            <i class="fa-solid fa-tag me-1" aria-hidden="true"></i>
                            <?= __('Contexte / Type de commentaire') ?>
                        </label>
                        <select id="comment-type" name="type" class="form-select form-select-sm mb-2" required>
                            <option value="GENERAL" selected><?= __('Général') ?></option>
                            <option value="OBSERVATION"><?= __('Observation') ?></option>
                            <option value="HIRING_REASON"><?= __('Motif de recrutement') ?></option>
                            <option value="PART_TIME"><?= __('Temps partiel / Répartition') ?></option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="comment-content" class="form-label fw-semibold fs-7 mb-1">
                            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>
                            <?= __('Message') ?>
                        </label>
                        <textarea
                            id="comment-content"
                            name="content"
                            class="form-control"
                            rows="3"
                            placeholder="<?= __('Saisissez votre commentaire ici...') ?>"
                            required></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-sm px-3" id="btn-submit-comment">
                            <i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i>
                            <?= __('Envoyer') ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
