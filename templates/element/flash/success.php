<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
echo $this->element('flash/toast', [
    'message' => $message,
    'params' => $params,
    'variant' => 'success',
]);
