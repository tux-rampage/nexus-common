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

namespace Rampage\Nexus\ApiClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Rampage\Nexus\ApiClient\Middleware\AuthenticateMiddleware;
use Rampage\Nexus\ApiClient\Middleware\JsonResponseMiddleware;

/**
 * Implements the Rest client for api calls
 */
class RestClient extends HttpClient
{
    /**
     * Constructor
     */
    public function __construct(AuthenticateStrategy $signRequestStrategy, callable $handler = null)
    {
        $handlerStack = new HandlerStack();
        $handlerStack->setHandler($handler?: new CurlHandler());
        $handlerStack->push(new GuzzleMiddlewareAdapter(new JsonResponseMiddleware()));
        $handlerStack->push(new GuzzleMiddlewareAdapter(new AuthenticateMiddleware($signRequestStrategy)));

        parent::__construct([
            'handler' => $handlerStack
        ]);
    }
}
