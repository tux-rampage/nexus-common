<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Entities;

use Rampage\Nexus\Package\PackageInterface;

use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\UnexpectedValueException;

use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;
use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\InvalidArgumentException;


/**
 * Defines a deployed application instance
 *
 * This entity is aggregated by the deploy target
 */
class ApplicationInstance
{
    const STATE_DEPLOYED = 'deployed';
    const STATE_ERROR = 'error';
    const STATE_PENDING = 'pending';
    const STATE_STAGING = 'staging';
    const STATE_ACTIVATING = 'activating';
    const STATE_REMOVING = 'removing';
    const STATE_DEACTIVATING = 'deactivating';
    const STATE_REMOVED = 'removed';
    const STATE_INACTIVE = 'inactive';
    const STATE_WORKING = 'working'; // Aggregated state
    const STATE_UNKNOWN = 'unknown';

    /**
     * Internal application identifier
     *
     * @var string
     */
    private $id = null;

    /**
     * The human readable label of this instance
     *
     * @var string
     */
    private $label = null;

    /**
     * The current application state
     *
     * This might be computed across all nodes
     *
     * @var string
     */
    private $state = self::STATE_PENDING;

    /**
     * The application that is deployed with this instance
     *
     * @var Application
     */
    private $application = null;

    /**
     * The currently deployed package
     *
     * @var PackageInterface
     */
    private $package = null;

    /**
     * The previously deployed application package
     *
     * @var PackageInterface
     */
    private $previousPackage = null;

    /**
     * The vhost within the deploy target
     *
     * @var string
     */
    private $vhost = null;

    /**
     * The target path within the vhost
     *
     * @var string
     */
    private $path = '/';

    /**
     * The application flavor used by the deploy strategy to optimize the created config
     *
     * @var string
     */
    private $flavor = null;

    /**
     * User provided parameters
     *
     * @var array
     */
    private $userParameters = [];

    /**
     * Pervious user parameters
     *
     * @var array
     */
    private $previousUserParameters = null;

    /**
     * @var bool
     */
    private $isRemoved = false;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        Application $application,
        string $id,
        VHost $vhost = null,
        string $path = null
    ) {
        if (!preg_match('~^[a-z0-9-_]+$~i', $id)) {
            throw new InvalidArgumentException('Bad application instance identifier: ' . $id);
        }

        if ($path) {
            if (!preg_match('~^/?[a-z0-9-_]+(/[a-z0-9-_]+)*/?$~i', $path)) {
                throw new InvalidArgumentException('Bad application path: ' . $path);
            }

            $path = '/' . trim($path, '/') . '/';
        }

        $this->application = $application;
        $this->id = $id;
        $this->path = $path? : '/';
        $this->vhost = $vhost? $vhost->getId() : null;
    }

    /**
     * Returns the application identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns the VHost ID for this application
     */
    public function getVHost(): string
    {
        return $this->vhost;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the application this instance references
     */
    public function getApplication(): Application
    {
        if (!$this->application) {
            throw new LogicException('Missing application instance');
        }

        return $this->application;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    public function setPackage(PackageInterface $package): void
    {
        if (!$this->application->hasPackage($package)) {
            throw new LogicException(sprintf('Package %s does not provide application %s', $this->package->getId(), $this->application->getName()));
        }

        $this->previousPackage = $this->package;
        $this->previousUserParameters = $this->userParameters;
        $this->package = $package;
    }

    public function getPreviousPackage(): PackageInterface
    {
        return $this->previousPackage;
    }

    public function getFlavor(): string
    {
        return $this->flavor;
    }

    public function setFlavor(string $flavor): void
    {
        $this->flavor = ($flavor !== null)? (string)$flavor : null;
    }

    public function getUserParameters(): iterable
    {
        return $this->userParameters;
    }

    public function setUserParameters(iterable $parameters): void
    {
        $this->userParameters = $parameters;
    }

    public function getPreviousParameters(): iterable
    {
        return $this->previousParameters;
    }

    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    /**
     * Perform application removal
     */
    public function remove()
    {
        $this->state = self::STATE_REMOVING;
        $this->isRemoved = true;
        return $this;
    }

    /**
     * Rollback to the previous instance state
     *
     * @throws LogicException
     */
    public function rollback(): void
    {
        if (!$this->previousPackage) {
            throw new LogicException('Cannot roll back without previous package');
        }

        $this->package = $this->previousPackage;
        $this->userParameters = $this->previousUserParameters? : [];
        $this->previousPackage = null;
        $this->previousUserParameters = null;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
}
