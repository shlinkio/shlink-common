<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManagerInterface;

class CloseDbConnectionMiddleware implements MiddlewareInterface
{
    private ReopeningEntityManagerInterface $em;

    public function __construct(ReopeningEntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->em->open();

        try {
            return $handler->handle($request);
        } finally {
            $this->em->getConnection()->close();
            $this->em->clear();
        }
    }
}
