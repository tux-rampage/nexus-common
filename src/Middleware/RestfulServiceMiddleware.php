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

namespace Rampage\Nexus\Middleware;

use Rampage\Nexus\Exception\Http\BadRequestException;
use Rampage\Nexus\Entities\Api\ArrayExportableInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response;

use Zend\Stratigility\MiddlewareInterface;
use Zend\Stdlib\ArraySerializableInterface;


/**
 * Implements the middleware for invoking REST services
 */
class RestfulServiceMiddleware implements MiddlewareInterface
{
    /**
     * @var object
     */
    private $service;

    /**
     * The service implementation to use
     *
     * @param object $service
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Returns the service name for the middleware pipe
     *
     * @param string $class The name of the implementing class or service
     * @return string
     */
    public static function getMiddlewareServiceName($class)
    {
        return __CLASS__ . ':' . $class;
    }

    /**
     * @return string[]
     */
    public function getSupportedHttpMethods()
    {
        $methods = ['get', 'put', 'post', 'delete', 'head'];
        $methods = array_filter($methods, function($method) {
            return method_exists($this->service, $method);
        });

        return array_map('strtoupper', $methods);
    }

    /**
     * @param callable $handler
     * @throws BadRequestException
     */
    private function notFound(callable $handler = null)
    {
        if (!$handler) {
            throw new BadRequestException('Not Found', 404);
        }

        return $handler();
    }

    /**
     * @param ServerRequestInterface $request
     * @return boolean
     */
    private function isPrettyPrintRequested(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();
        return (isset($query['pretty']) && ($query['pretty'] != '0'));
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function buildOptionsResponse()
    {
        $response = new EmptyResponse();
        return $response->withHeader('Allow', $this->getSupportedHttpMethods());
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    private function prepareJsonData($result)
    {
        if ($result instanceof ArrayExportableInterface) {
            $result = $result->toArray();
        } else if ($result instanceof ArraySerializableInterface) {
            $result = $result->getArrayCopy();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $method = strtolower($request->getMethod());

        if (!method_exists($this->service, $method)) {
            if ($method == 'options') {
                return $this->buildOptionsResponse();
            }

            return $response->withStatus(BadRequestException::NOT_ALLOWED);
        }

        $result = $this->service->$method($request);

        if ($result === null) {
            return $this->notFound($out);
        }

        if ($result instanceof StreamInterface) {
            $result = new Response($result);
        }

        if (!$result instanceof ResponseInterface) {
            $flags = JsonResponse::DEFAULT_JSON_FLAGS;

            if ($this->isPrettyPrintRequested($request)) {
                $flags = $flags | JSON_PRETTY_PRINT;
            }

            $result = new JsonResponse($this->prepareJsonData($result));
        }

        return $result;
    }
}