<?php

declare(strict_types=1);

/**
 * The "Suggest a project" issue form, as plain text.
 */
function suggestTemplate(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/.github/ISSUE_TEMPLATE/suggest-a-project.yml');
}

it('asks for each maker on their own line, with full name and GitHub username', function () {
    $template = suggestTemplate();

    expect($template)
        ->toMatch('/- type: textarea\n    id: makers\n/')
        ->toContain('label: Who made it')
        ->toContain('One maker per line: the full name, then the GitHub username.')
        ->toContain('Jane Smith, janesmith');
});
