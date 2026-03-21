<?php

test('workflow php versions stay aligned with composer requirements', function () {
    $composer = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($composer['require']['php'])->toBe('^8.5');

    foreach ([
        '.github/workflows/tests.yml',
        '.github/workflows/lint.yml',
        '.github/workflows/deploy.yml',
    ] as $workflow) {
        expect(file_get_contents(base_path($workflow)))
            ->toContain("php-version: '8.5'")
            ->not->toContain('composer.fluxui.dev');
    }
});

test('repository excludes server bootstrap artifacts and ignores generated runtime artifacts', function () {
    foreach ([
        'scripts/bootstrap_linode_server.sh',
        'setup_server.md',
    ] as $path) {
        expect(file_exists(base_path($path)))->toBeFalse();
    }

    expect(file_exists(base_path('.github/scripts/deploy.sh')))->toBeTrue();
    expect(file_exists(base_path('.github/workflows/deploy.yml')))->toBeTrue();

    expect(file_get_contents(base_path('.gitignore')))
        ->toContain('/bootstrap/cache/*.php')
        ->toContain('/storage/framework/cache/data/*')
        ->toContain('/storage/framework/sessions/*')
        ->toContain('/storage/framework/testing/*')
        ->toContain('/storage/framework/views/*')
        ->toContain('/storage/logs/*.log');
});
