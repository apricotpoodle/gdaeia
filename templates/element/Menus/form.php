<?php
/**
 * Élément formulaire réutilisable pour Add et Edit
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Menu $menu
 * @var array $parentMenus
 */
?>
<?= $this->Form->create($menu, ['class' => 'needs-validation']) ?>
    <fieldset>
        <legend><?= __('Paramètres du Menu') ?></legend>
        <?php
            echo $this->Form->control('parent_id', ['options' => $parentMenus, 'empty' => '(Racine)']);
            echo $this->Form->control('name', ['label' => 'Nom du menu', 'required' => true]);
            echo $this->Form->control('url', ['label' => 'URL (ex: /users/index)']);
            echo $this->Form->control('active', ['type' => 'checkbox', 'label' => 'Actif']);
            echo $this->Form->control('disabled', ['type' => 'checkbox', 'label' => 'Désactivé']);
            echo $this->Form->control('dividor_before', ['type' => 'checkbox', 'label' => 'Séparateur avant']);
        ?>
    </fieldset>
    <div class="form-actions">
        <?= $this->Form->button('<i class="fas fa-save"></i> ' . __('Enregistrer'), ['escapeTitle' => false, 'class' => 'btn btn-primary']) ?>
        <?= $this->Html->link(__('Annuler'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
    </div>
<?= $this->Form->end() ?>
