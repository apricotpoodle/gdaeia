<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Menu $menu
 * @var array $parentMenus
 */
$this->assign('title', __('Modifier le menu'));
?>
<div class="menus form content">
    <?= $this->element('Menus/form', ['menu' => $menu, 'parentMenus' => $parentMenus]) ?>
</div>
