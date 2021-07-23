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
        #'@PHP80Migration' => true,
        'phpdoc_to_comment' => false,
        'yoda_style'=>['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
;
