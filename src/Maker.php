<?php

declare(strict_types=1);

namespace PhpLives;

final readonly class Maker
{
    public function __construct(
        public string $name,
        public ?string $github = null,
    ) {}
}
