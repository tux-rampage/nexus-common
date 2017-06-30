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

namespace Rampage\Nexus\ServiceFactory\Middleware;

use Zend\Expressive\Container\ApplicationFactory;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;

/**
 * Factory for expressive aplication instances (that can act as middleware on its own
 */
abstract class AbstractApplicationFactory extends ApplicationFactory
{
    /**
     * @param Application $application
     * @param ContainerInterface $container
     */
    abstract protected function createMiddlewarePipe(Application $application, ContainerInterface $container);

    /**
     * @param Application $application
     * @param ContainerInterface $container
     */
    abstract protected function createRoutingDefinition(Application $application, ContainerInterface $container);

    /**
     * {@inheritDoc}
     * @see \Zend\Expressive\Container\ApplicationFactory::__invoke()
     */
    public function __invoke(ContainerInterface $container)
    {
        $application = parent::__invoke($container);

        $this->createMiddlewarePipe($application, $container);
        $this->createRoutingDefinition($application, $container);

        return $application;
    }
}
