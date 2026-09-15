<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Role $role */
/** @var \Authorization\IdentityInterface|null $identity */
$this->assign('title', __('Rôle #{0}', $role->id));
?>
<div class="row">
    <aside class="col-md-3 side-nav mb-3">
        <?= $this->Action->render(\App\View\Action\RolesActions::index()) ?>
        <?= $this->Action->render(\App\View\Action\RolesActions::edit($role)) ?>
    </aside>
    <div class="col-md-9 content">
        <h1 class="h3"><?= h($role->name) ?></h1>
        <dl class="row">
            <dt class="col-sm-3"><?= __('Code') ?></dt><dd class="col-sm-9"><?= h($role->code) ?></dd>
            <dt class="col-sm-3"><?= __('Clé de tri') ?></dt><dd class="col-sm-9"><?= h($role->sort) ?></dd>
            <dt class="col-sm-3"><?= __('Rôle socle') ?></dt><dd class="col-sm-9"><?= $role->base ? __('Oui') : __('Non') ?></dd>
        </dl>
    </div>
</div>
