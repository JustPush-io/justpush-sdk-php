<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'bootstrap/cache',
        'node_modules',
        'storage',
        'vendor',
    ]);

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony:risky' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal',
            ],
        ],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'increment_style' => [
            'style' => 'post',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'native_function_invocation' => [
            'include' => [],
        ],
        'native_constant_invocation' => [
            'fix_built_in' => false,
        ],
        'no_null_property_initialization' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_unused_imports' => true,
        'phpdoc_align' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => [
            'sort_algorithm' => 'none',
            'null_adjustment' => 'always_last'
        ],
        'phpdoc_summary' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'random_api_migration' => true,
        'self_accessor' => false,
        'strict_comparison' => true,
        'strict_param' => true,
        'use_arrow_functions' => false,
        'yoda_style' => false,
    ]);

return $config;
