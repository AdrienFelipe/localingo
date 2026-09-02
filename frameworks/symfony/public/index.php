<?php


use App\Shared\Framework\Symfony\SymfonyKernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new SymfonyKernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
