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

use Rampage\Nexus\Package\ParameterInterface;
use Zend\Stdlib\Parameters;

/**
 * Implements a persistable package parameter
 *
 * @return array
 */
class PackageParameter implements ParameterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $type = 'text';

    /**
     * @var string
     */
    private $default = null;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $valueOptions = null;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @param string|ParameterInterface $nameOrParameter
     */
    public function __construct($nameOrParameter)
    {
        if ($nameOrParameter instanceof ParameterInterface) {
            $this->copy($nameOrParameter);
        } else {
            $this->setName($nameOrParameter);
        }
    }

    /**
     * Copy from another parameter implementation
     */
    protected function copy(ParameterInterface $parameter): void
    {
        $this->default = $parameter->getDefault();
        $this->label = $parameter->getLabel();
        $this->name = $parameter->getName();
        $this->options = null;
        $this->required = $parameter->isRequired();
        $this->type = $parameter->getType();
        $this->options = $parameter->getOptions();

        if ($parameter->hasValueOptions()) {
            $this->valueOptions = $parameter->getValueOptions();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    public function getLabel(): string
    {
        if (!$this->label) {
            return $this->getName();
        }

        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function hasValueOptions(): bool
    {
        return ($this->valueOptions !== null);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getValueOptions(): array
    {
        return $this->valueOptions;
    }

    public function removeValueOptions(): void
    {
        $this->valueOptions = null;
    }

    public function addValueOption(string $option, string $label = null): void
    {
        $option = $option;

        if ($label === null) {
            $label = $option;
        }

        if (!is_array($this->valueOptions)) {
            $this->valueOptions = [];
        }

        $this->valueOptions[$option] = $label;
    }

    public function setValueOptions(iterable $options): void
    {
        $this->valueOptions = [];

        foreach ($options as $key => $value) {
            $this->addValueOption($key, $value);
        }
    }

    public function setOption(string $name, string $value): void
    {
        $this->options[$name] = $value;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }
}
