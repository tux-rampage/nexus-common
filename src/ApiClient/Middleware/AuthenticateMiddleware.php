<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\ApiClient\Middleware;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Rampage\Nexus\ApiClient\AuthenticateStrategy;
use Rampage\Nexus\ApiClient\GuzzleMiddleware;

class AuthenticateMiddleware implements GuzzleMiddleware
{
    /**
     * @var AuthenticateStrategy
     */
    private $strategy;

    public function __construct(AuthenticateStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function process(RequestInterface $request, callable $next, array $options): PromiseInterface
    {
        $request = $this->strategy->authenticate($request, $options);
        return $next($request, $options);
    }
}