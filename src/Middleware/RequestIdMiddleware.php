<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Common\Middleware;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class RequestIdMiddleware implements MiddlewareInterface, ProcessorInterface
{
    private const HEADER = 'X-Request-Id';
    public const ATTRIBUTE = 'request_id';

    /** Holds the value for current request, in order to use it for every generated log entry */
    private string $currentRequestId = '-';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->currentRequestId = $request->hasHeader(self::HEADER)
            ? $request->getHeaderLine(self::HEADER)
            : Uuid::uuid4()->toString();

        // Set the request id to the request, in case it's needed by some subsequent middleware
        $requestWithAttribute = $request->withAttribute(self::ATTRIBUTE, $this->currentRequestId);

        // Set request ID in response before returning it
        return $handler->handle($requestWithAttribute)->withHeader(self::HEADER, $this->currentRequestId);
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        // Overwrite request ID with currently set value, as it changes on every request
        $record->extra[self::ATTRIBUTE] = $this->currentRequestId;
        return $record;
    }

    // The getter and setter defined here are used to forward a request ID generated at the beginning of a request, to
    // every job scheduled during that request,
    //  * With the getter we read the value and pass it serialized with the rest of the job's payload.
    //  * With the setter we set it at the beginning of the job, into that job's RequestIdMiddleware instance.

    public function setCurrentRequestId(string $requestId): void
    {
        $this->currentRequestId = $requestId;
    }

    public function currentRequestId(): string
    {
        return $this->currentRequestId;
    }
}
