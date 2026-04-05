<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common\ObjectMapper;

use Cake\Chronos\Chronos;

readonly class MockModel
{
    public function __construct(
        public string|null $foo = null,
        public Chronos|null $bar = null,
    ) {
    }
}
