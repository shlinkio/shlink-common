<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Common\Doctrine\ReopeningEntityManager;

class CloseDbConnectionMiddleware implements MiddlewareInterface
{
    /** @var ReopeningEntityManager */
    private $em;

    public function __construct(ReopeningEntityManager $em)
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
