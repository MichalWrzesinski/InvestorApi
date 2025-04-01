<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_order' => true,
        'no_unused_imports' => true,
        'fully_qualified_strict_types' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_align' => ['align' => 'vertical'],
        'yoda_style' => true,
        'single_line_throw' => true,
    ])
    ->setFinder($finder);
