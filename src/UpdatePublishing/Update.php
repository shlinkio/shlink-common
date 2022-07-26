<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\UpdatePublishing;

final class Update
{
    private function __construct(public readonly string $topic, public readonly array $payload)
    {
    }

    public static function forTopicAndPayload(string $topic, array $payload): self
    {
        return new self($topic, $payload);
    }
}
