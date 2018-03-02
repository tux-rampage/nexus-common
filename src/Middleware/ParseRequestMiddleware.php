<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Rampage\Nexus\Exception\RuntimeException;

/**
 * Parses the request body according to the content type
 */
class ParseRequestMiddleware implements MiddlewareInterface
{
    /**
     * Checks the content type if it is suitable for JSON
     */
    protected function isJsonType(string $contentType): bool
    {
        return (bool)preg_match('~^application/json(;|$)~i', $contentType);
    }

    /**
     * Returns the data array from JSON encoded body
     */
    private function decodeJson(string $body): array
    {
        if ($body instanceof StreamInterface) {
            $body = $body->getContents();
        }

        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new RuntimeException('Failed to parse JSON body: ' . json_last_error_msg());
        }

        return $data;
    }

    protected function parseRequestBody(ServerRequestInterface $request): ServerRequestInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (!$this->isJsonType($contentType)) {
            return $request;
        }

        $data = $this->decodeJson($request->getBody());
        return $request->withParsedBody($data);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->parseRequestBody($request);
        return $handler->handle($request);
    }
}
