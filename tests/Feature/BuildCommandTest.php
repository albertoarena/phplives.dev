<?php

declare(strict_types=1);

use Tests\FakeDevNull;

beforeEach(function () {
    $this->tmp = sys_get_temp_dir().'/phplives-build-'.bin2hex(random_bytes(4));
    mkdir($this->tmp.'/entries', 0777, true);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->tmp));
});

function build(string $tmp, ?string $devnull = null): array
{
    $command = implode(' ', array_map(escapeshellarg(...), [
        PHP_BINARY, dirname(__DIR__, 2).'/bin/build',
        "--entries={$tmp}/entries", "--out={$tmp}/dist", '--today=2026-10-16',
        '--devnull='.($devnull ?? FakeDevNull::deadUrl()),
    ]));
    exec($command.' 2>&1', $output, $status);

    return [$status, implode("\n", $output)];
}

it('writes the site into the output folder', function () {
    writeEntry($this->tmp.'/entries', '2026-09-01-pest-5.php');

    [$status, $output] = build($this->tmp);

    expect($status)->toBe(0)
        ->and($output)->toContain('Built 5 pages from 1 entry')
        ->and($this->tmp.'/dist/index.html')->toBeFile()
        ->and($this->tmp.'/dist/archive/index.html')->toBeFile()
        ->and($this->tmp.'/dist/privacy/index.html')->toBeFile()
        ->and($this->tmp.'/dist/credits/index.html')->toBeFile()
        ->and($this->tmp.'/dist/site.css')->toBeFile()
        ->and($this->tmp.'/dist/favicon.svg')->toBeFile()
        ->and($this->tmp.'/dist/404.html')->toBeFile()
        ->and($this->tmp.'/dist/share.png')->toBeFile()
        ->and(getimagesize($this->tmp.'/dist/share.png'))->toMatchArray([0 => 1200, 1 => 630])
        ->and($this->tmp.'/dist/img/devnull-wallpaper-log-1280.webp')->toBeFile()
        ->and(getimagesize($this->tmp.'/dist/img/devnull-wallpaper-log-1920.webp'))->toMatchArray([0 => 1920, 1 => 1080])
        ->and($this->tmp.'/dist/fonts/archivo-latin.woff2')->toBeFile()
        ->and($this->tmp.'/dist/fonts/jetbrains-mono-latin.woff2')->toBeFile()
        ->and($this->tmp.'/dist/fonts/OFL-Archivo.txt')->toBeFile()
        ->and($this->tmp.'/dist/fonts/OFL-JetBrainsMono.txt')->toBeFile()
        ->and(file_get_contents($this->tmp.'/dist/index.html'))->toContain('Pest 5');
});

it('fails and lists the problems when an entry is not valid', function () {
    writeEntry($this->tmp.'/entries', '2026-09-01-pest-5.php', ['stack' => 'drupal']);

    [$status, $output] = build($this->tmp);

    expect($status)->toBe(1)
        ->and($output)->toContain('2026-09-01-pest-5.php: Unknown stack: drupal.')
        ->and($this->tmp.'/dist')->not->toBeDirectory();
});

it('builds the real entries', function () {
    exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__, 2).'/bin/build')
        .' --out='.escapeshellarg($this->tmp.'/dist')
        .' --devnull='.escapeshellarg(FakeDevNull::deadUrl()).' 2>&1', $output, $status);

    expect($status)->toBe(0)->and($this->tmp.'/dist/index.html')->toBeFile();
});

it('links makers and shows posts from /dev/null', function () {
    writeEntry($this->tmp.'/entries', '2026-09-01-pest-5.php', ['makers' => [
        ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ['name' => 'Brent Roose', 'github' => 'brendt'],
    ]]);

    [$status, $output] = build($this->tmp, FakeDevNull::url());
    $html = file_get_contents($this->tmp.'/dist/index.html');

    expect($status)->toBe(0)
        ->and($output)->toContain('/dev/null: 1 of 2 makers have a profile. 1 post links to phplives.dev.')
        ->and($html)->toContain('href="https://dev-null.sh/@nunomaduro"')
        ->and($html)->not->toContain('href="https://dev-null.sh/@brendt"')
        ->and($html)->toContain('https://dev-null.sh/@albertoarena/commits/c2');
});

it('still builds when /dev/null is down', function () {
    writeEntry($this->tmp.'/entries', '2026-09-01-pest-5.php');

    [$status, $output] = build($this->tmp);
    $html = file_get_contents($this->tmp.'/dist/index.html');

    expect($status)->toBe(0)
        ->and($output)->toContain('/dev/null did not answer. The site was built without /dev/null links and posts.')
        ->and($html)->toContain('Pest 5')
        ->and($html)->not->toContain('class="strip"');
});
