<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Entity;

abstract class AbstractEntity
{
    protected ?string $id = null;

    public function getId(): string
    {
        return $this->id ?? '';
    }

    /**
     * @internal
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
