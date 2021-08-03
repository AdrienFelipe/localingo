<?php


use App\Shared\Framework\Symfony5\Symfony5Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Symfony5Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
