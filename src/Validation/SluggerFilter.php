<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Validation;

use Cocur\Slugify;
use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;

use function is_string;

class SluggerFilter implements FilterInterface
{
    private Slugify\SlugifyInterface $slugger;

    public function __construct(?Slugify\SlugifyInterface $slugger = null)
    {
        $this->slugger = $slugger ?: new Slugify\Slugify(['lowercase' => false]);
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
        return is_string($value) ? $this->slugger->slugify($value) : $value;
    }
}
