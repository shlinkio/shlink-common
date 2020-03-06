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

    public function __construct(?Slugger\SluggerInterface $slugger = null)
    {
        $this->slugger = $slugger ?: new Slugger\AsciiSlugger();
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        return is_string($value) ? (string) $this->slugger->slug($value) : $value;
    }
}
