<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;
use Symfony\Component\String\Slugger;

use function is_string;

class SluggerFilter implements FilterInterface
{
    private Slugger\SluggerInterface $slugger;

    public function __construct(Slugger\SluggerInterface|null $slugger = null)
    {
        $this->slugger = $slugger ?? new Slugger\AsciiSlugger();
    }

    /**
     * @throws Exception\RuntimeException If filtering $value is impossible
     */
    public function filter(mixed $value): mixed
    {
        return is_string($value) ? $this->slugger->slug($value)->toString() : $value;
    }
}
