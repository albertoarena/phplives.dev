<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * The answer a link gave: a status, or why nothing answered.
 */
final readonly class LinkStatus
{
    public function __construct(public int $code, public ?string $failure = null) {}

    /**
     * A link works when its final answer, after redirects, is a success.
     */
    public function ok(): bool
    {
        return $this->code >= 200 && $this->code < 300;
    }

    /**
     * A server error, a rate limit or no answer at all usually means the other
     * site has a problem for a while. Many sites also refuse every request from
     * a script with a 403, while they show the page to a browser. The entry may
     * still be right.
     */
    public function warns(): bool
    {
        return $this->code >= 500
            || $this->code === 429
            || $this->code === 403
            || ($this->code === 0 && $this->failure === Http::NO_ANSWER);
    }

    /**
     * A warning that repeats on enough runs in a row counts as broken, so a site
     * that stays down still gets noticed. A 403 does not, because a site that
     * refuses scripts refuses them every week.
     */
    public function escalates(): bool
    {
        return $this->warns() && $this->code !== 403;
    }

    /**
     * Anything else that is not a success means the entry needs an edit: a
     * missing page, a domain that is gone, a bad certificate.
     */
    public function fails(): bool
    {
        return ! $this->ok() && ! $this->warns();
    }

    public function label(): string
    {
        return $this->code === 0 ? (string) $this->failure : (string) $this->code;
    }
}
