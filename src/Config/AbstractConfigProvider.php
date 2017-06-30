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

namespace Rampage\Nexus\Config;

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
abstract class AbstractConfigProvider
{
    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var bool
     */
    protected $devMode;

    /**
     * Environment Variables
     *
     * @var ParametersInterface
     */
    protected $env;

    /**
     * @param string $cacheFile
     */
    public function __construct(array $env = null, $cacheFile = null)
    {
        $this->cacheFile = $cacheFile;
        $this->env = new Parameters($env?: $_SERVER);
        $this->devMode = $this->isDevMode();
    }

    /**
     * @return boolean
     */
    protected function isDevMode()
    {
        return ($this->env->get('APPLICATION_DEVELOPMENT')
                || $this->env->get('DEVELOPER_MODE'));
    }

    /**
     * Returns all providers to aggregate
     *
     * @return array
     */
    abstract protected function getProviders();

    /**
     * @return array
     */
    public function getConfig()
    {
        $aggregator = new ConfigAggregator($this->getProviders(), $this->cacheFile);
        return $aggregator->getMergedConfig();
    }

    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->getConfig();
    }
}
