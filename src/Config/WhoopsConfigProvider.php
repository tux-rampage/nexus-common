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

namespace Rampage\Nexus\Config;

use Zend\Expressive\Middleware\ErrorResponseGenerator;
use Zend\Expressive\Container\WhoopsErrorResponseGeneratorFactory;
use Zend\Expressive\Container\WhoopsFactory;
use Zend\Expressive\Container\WhoopsPageHandlerFactory;

/**
 * Implements a config provider for dev handling
 */
class WhoopsConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        if (!class_exists('Whoops\Run')) {
            return [];
        }

        return [
            'dependencies' => [
                'factories' => [
                    ErrorResponseGenerator::class => WhoopsErrorResponseGeneratorFactory::class,
                    'Zend\Expressive\Whoops' => WhoopsFactory::class,
                    'Zend\Expressive\WhoopsPageHandler' => WhoopsPageHandlerFactory::class,
                ],
            ],

            'whoops' => [
                'json_exceptions' => [
                    'display'    => true,
                    'show_trace' => true,
                    'ajax_only'  => true,
                ],
            ],
        ];
    }
}
