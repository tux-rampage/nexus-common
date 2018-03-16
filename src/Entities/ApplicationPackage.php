<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Package\ArrayExportableTrait as ArrayExportablePackageTrait;
use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Package\ParameterInterface;

/**
 * Application Package Entity
 */
class ApplicationPackage implements PackageInterface
{
    use ArrayExportablePackageTrait;

    /**
     * @var string
     */
    private $id = null;

    /**
     * The path to the archive
     *
     * @var string
     */
    protected $archive = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $version = null;

    /**
     * @var string
     */
    protected $isStable = true;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $documentRoot = null;

    /**
     * @var ParameterInterface[]|ArrayCollection
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var array
     */
    protected $extra = [];


    public function __construct(PackageInterface $package = null)
    {
        $this->parameters = new ArrayCollection();

        if ($package) {
            $this->copy($package);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Copy information from the given package
     */
    private function copy(PackageInterface $package): void
    {
        if ($this->getType() && ($package->getType() != $this->getType())) {
            throw new UnexpectedValueException('Incompatible package types');
        }

        $this->id = $package->getId();
        $this->documentRoot = $package->getDocumentRoot();
        $this->extra = $package->getExtra();
        $this->name = $package->getName();
        $this->parameters = [];
        $this->type = $package->getType();
        $this->version = $package->getVersion();
        $this->isStable = $package->isStable();
        $this->variables = $package->getVariables();

        foreach ($package->getParameters() as $param) {
            $this->parameters[$param->getName()] = new PackageParameter($param);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentRoot(): string
    {
        return $this->documentRoot;
    }

    public function getExtra(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->extra;
        }

        if (!isset($this->extra[$name])) {
            return $default;
        }

        return $this->extra[$name];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): iterable
    {
        return $this->parameters->toArray();
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function isStable(): bool
    {
        return $this->isStable;
    }

    /**
     * Override stability
     */
    public function setIsStable(bool $flag): void
    {
        $this->isStable = $flag;
    }
}
