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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Rampage\Nexus\Exception\RuntimeException;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

/**
 * Parses the request body according to the content type
 */
class ParseRequestMiddleware implements MiddlewareInterface
{
    /**
     * Checks the content type if it is suitable for JSON
     *
     * @param   string  $contentType    The content type / mime type
     * @return  boolean                 True if the content type designates JSON data, false otherwise
     */
    protected function isJsonType($contentType)
    {
        return (bool)preg_match('~^application/json(;|$)~i', $contentType);
    }

    /**
     * Returns the data array from JSON encoded body
     *
     * @param   RequestInterface    $request
     * @throws  UnexpectedValueException
     * @return  array
     */
    private function decodeJson($body)
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

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function parseRequestBody(ServerRequestInterface $request)
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (!$this->isJsonType($contentType)) {
            return $request;
        }

        $data = $this->decodeJson($request->getBody());
        return $request->withParsedBody($data);
    }


    /**
     * {@inheritDoc}
     * @see \Interop\Http\ServerMiddleware\MiddlewareInterface::process()
     */
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $request = $this->parseRequestBody($request);
        return $delegate->process($request);
    }
}
