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

use Zend\Di\ConfigProvider as DiConfigProvider;
use Zend\Stdlib\Parameters;

/**
 * Implements the common config provider
 */
class CommonConfigProvider extends AbstractConfigProvider
{
    /**
     * @var Parameters
     */
    private $server;

    /**
     * @param array $server
     */
    public function __construct(array $server = null)
    {
        $this->server = new Parameters($server? : $_SERVER);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Config\AbstractConfigProvider::getProviders()
     */
    protected function getProviders()
    {
        $providers = [
            new DiConfigProvider(),
            new PhpDirectoryProvider(__DIR__ . '/../../config'),
        ];

        if ($this->server->get('APPLICATION_DEVELOPMENT')) {
            $providers[] = new WhoopsConfigProvider();
        }

        return $providers;
    }
}
