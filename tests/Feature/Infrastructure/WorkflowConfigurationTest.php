<?php

test('workflow php versions stay aligned with composer requirements', function () {
    $composer = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($composer['require']['php'])->toBe('^8.5');

    expect(file_get_contents(base_path('.github/workflows/tests.yml')))
        ->toContain("php-version: '8.5'");

    expect(file_get_contents(base_path('.github/workflows/lint.yml')))
        ->toContain("php-version: '8.5'");

    expect(file_get_contents(base_path('.github/workflows/deploy.yml')))
        ->toContain("php-version: '8.5'");
});
