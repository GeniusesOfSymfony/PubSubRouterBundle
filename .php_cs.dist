<?php

declare(strict_types=1);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true, // Set to false when releasing 2.0
        'declare_strict_types' => false, // Set to true when releasing 2.0
        'fopen_flags' => false,
        'ordered_imports' => true,
        'protected_to_private' => true,
        'void_return' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->notPath('Fixtures/dumper') // Generated fixtures
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;
