<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2018 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\ApiClient\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rampage\Nexus\ApiClient\GuzzleMiddleware;

class JsonResponseMiddleware implements GuzzleMiddleware
{
    public function process(RequestInterface $request, callable $next, array $options): PromiseInterface
    {
        return $next($request, $options)->then(function(ResponseInterface $response) {
            return $response;
        });
    }
}