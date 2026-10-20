<?php

declare(strict_types=1);

use PhpLives\Entries;

it('has only valid entry files', function () {
    $entries = Entries::load(dirname(__DIR__, 2).'/entries', new DateTimeImmutable('today'));

    expect($entries->all())->not->toBeEmpty();
});
