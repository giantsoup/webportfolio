<?php

test('ci workflow php versions stay aligned with composer requirements', function () {
    $composer = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($composer['require']['php'])->toBe('^8.5');

    foreach ([
        '.github/workflows/tests.yml',
        '.github/workflows/lint.yml',
    ] as $workflow) {
        expect(file_get_contents(base_path($workflow)))
            ->toContain("php-version: '8.5'")
            ->not->toContain('composer.fluxui.dev');
    }
});

test('repository excludes committed infrastructure automation and ignores generated runtime artifacts', function () {
    foreach ([
        '.github/scripts/deploy.sh',
        '.github/workflows/deploy.yml',
        'scripts/bootstrap_linode_server.sh',
        'setup_server.md',
    ] as $path) {
        expect(file_exists(base_path($path)))->toBeFalse();
    }

    expect(file_get_contents(base_path('.gitignore')))
        ->toContain('/bootstrap/cache/*.php')
        ->toContain('/storage/framework/cache/data/*')
        ->toContain('/storage/framework/sessions/*')
        ->toContain('/storage/framework/testing/*')
        ->toContain('/storage/framework/views/*')
        ->toContain('/storage/logs/*.log');
});
