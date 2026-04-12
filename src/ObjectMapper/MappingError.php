<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\ObjectMapper;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use RuntimeException;
use Shlinkio\Shlink\Common\Exception\ExceptionInterface;

final class MappingError extends RuntimeException implements ErrorMessage, ExceptionInterface
{
    public static function withBody(string $body): self
    {
        return new self($body);
    }

    public function body(): string
    {
        return $this->message;
    }
}
