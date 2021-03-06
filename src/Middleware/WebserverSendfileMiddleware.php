<?php
/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rampage\Nexus\NullStream;

/**
 * Implements a middleware that will attempt to delegate file delivery responses
 * from PHP to the Webserver
 *
 * This is useful for delivering internal files via the webserver software (i.e. via X-Accel-Redirect in Nginx)
 * which may handle this way better.
 *
 * This may also release the occupied PHP process much faster.
 */
class WebserverSendfileMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @param iterable|string[] $mapping
     */
    public function __construct(iterable $mapping = [])
    {
        foreach ($mapping as $from => $to) {
            $this->mapPathPrefix($from, $to);
        }
    }

    public function mapPathPrefix(string $fromPathPrefix, string $toPathPrefix): void
    {
        $this->mapping[$fromPathPrefix] = $toPathPrefix;
    }

    /**
     * Create a sendfile response that deligates to the webserver
     */
    private function sendFile(
        ResponseInterface $response,
        string $streamUrl,
        string $fromPath,
        string $toPath
    ): ResponseInterface
    {
        $location = $toPath . substr($streamUrl, strlen($fromPath));

        return $response->withBody(new NullStream())
                        ->withHeader('X-Accel-Redirect', $location);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response->getStatusCode() != 200) {
            return $response;
        }

        $streamUrl = $response->getBody()->getMetadata('uri');
        if (!$streamUrl && (substr($streamUrl, 0, 6) == 'php://')) {
            return $response;
        }

        foreach ($this->mapping as $fromPath => $toPath) {
            if (strpos($streamUrl, $fromPath) === 0) {
                return $this->sendFile($response, $streamUrl, $fromPath, $toPath);
            }
        }

        return $response;
    }
}
