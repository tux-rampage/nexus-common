<?php
/**
 * Copyright (c) 2017 Axel Helmert
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
 * @copyright Copyright (c) 2017 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Api;

use Psr\Http\Message\RequestInterface;

class GuzzleMiddlewareAdapter
{
    private $middleware;

    public function __construct(GuzzleMiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    private function createNextCallback(callable $handler, RequestInterface $request, array $options)
    {
        return function(RequestInterface $newRequest = null) use ($handler, $request, $options) {
            return $handler($newRequest?: $request, $options);
        };
    }

    public function __invoke(callable $handler)
    {
        return function(RequestInterface $request, array $options = []) use ($handler) {
            $next = $this->createNextCallback($handler, $request, $options);
            $this->middleware->__invoke($request, $options, $next);
        };
    }
}
