<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Role $role */
$this->assign('title', __('Modifier le rôle #{0}', $role->id));
?>
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white"><h1 class="h4 mb-0"><?= h($this->fetch('title')) ?></h1></div>
    <div class="card-body"><?= $this->element('Roles/form', compact('role')) ?></div>
</div>
