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

namespace Rampage\Nexus\Package;

use Rampage\Nexus\Exception\LogicException;

/**
 * Provides the array exportable implementation for packages
 */
trait ArrayExportableTrait
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        /** @var PackageInterface $this */
        if (!$this instanceof PackageInterface) {
            throw new LogicException('This trait can only act on PackageInterface implementations');
        }

        $array = [
            'id' => $this->getId(),
            'documentRoot' => $this->getDocumentRoot(),
            'extra' => $this->getExtra(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'version' => $this->getVersion(),
            'variables' => $this->getVariables(),
            'parameters' => [],
        ];

        /* @var $parameter \Rampage\Nexus\Package\ParameterInterface */
        foreach ($this->getParameters() as $parameter) {
            $name = $parameter->getName();
            $array['parameters'][$name] = [
                'name' => $name,
                'label' => $parameter->getLabel(),
                'default' => $parameter->getDefault(),
                'type' => $parameter->getType(),
                'required' => $parameter->isRequired(),
            ];

            foreach ($parameter->getOptions() as $key => $value) {
                if (!is_scalar($value)) {
                    continue;
                }

                $array['parameters'][$name]['options'][$key] = $value;
            }

            if ($parameter->hasValueOptions()) {
                $array['parameters'][$name]['valueOptions'] = $parameter->getValueOptions();
            }
        }

        return $array;
    }


}