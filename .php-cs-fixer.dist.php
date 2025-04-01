<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_order' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'yoda_style' => true,
        'fully_qualified_strict_types' => true,
        'no_superfluous_phpdoc_tags' => true,
        'single_line_throw' => true,
    ])
    ->setFinder($finder);
