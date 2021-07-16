<?php

$finder = (new PhpCsFixer\Finder())
    ->in(exec('pwd'))
    ->exclude('active-frameworks')
    ->exclude('frameworks')
    ->exclude('etc')
    ->ignoreVCS(true)
    ->files()
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
