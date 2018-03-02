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
use Zend\Stdlib\Parameters;
use Psr\Http\Message\StreamInterface;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Represents a deployable application
 *
 * This is a logical grouping of all packages of a specific application.
 * It may never contain packages of other applications and it might not exist without at least one
 * Package instance
 */
class Application
{
    /**
     * The identifier
     *
     * This is the package name that groups all packages
     *
     * @var string
     */
    private $id = null;

    /**
     * The application label
     *
     * This may be the identifier (which is the package name) by default.
     *
     * @var string
     */
    private $label = null;

    /**
     * Represents the icon as binary data
     *
     * @var StreamInterface
     */
    private $icon = null;

    /**
     * @var ApplicationPackage[]|ArrayCollection
     */
    private $packages = [];

    /**
     * Construct
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->packages = new ArrayCollection();
    }

    /**
     * Returns the unique identifier of this application
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the application label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return StreamInterface
     */
    public function getIcon(): ?StreamInterface
    {
        return $this->icon;
    }

    public function setIcon(StreamInterface $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return ApplicationPackage[]
     */
    public function getPackages(): iterable
    {
        return $this->packages;
    }

    /**
     * Find a package by id
     */
    public function findPackage($packageId): ?PackageInterface
    {
        $filter = function(PackageInterface $item) use ($packageId) {
            return ($item->getId() == $packageId);
        };

        return $this->packages->filter($filter)->first();
    }

    /**
     * Check if this application has the requested package
     */
    public function hasPackage(PackageInterface $package): bool
    {
        return $this->packages->exists(function(PackageInterface $item) use ($package) {
            return ($item->getId() == $package->getId());
        });
    }
}
