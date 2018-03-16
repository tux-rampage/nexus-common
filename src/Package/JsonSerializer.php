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

/**
 * Provides json exportability for packages
 */
final class JsonSerializer
{
    public function extract(PackageInterface $package): array
    {
        $array = [
            'id' => $package->getId(),
            'documentRoot' => $package->getDocumentRoot(),
            'extra' => $package->getExtra(),
            'name' => $package->getName(),
            'type' => $package->getType(),
            'version' => $package->getVersion(),
            'variables' => $package->getVariables(),
            'parameters' => [],
        ];

        /* @var $parameter \Rampage\Nexus\Package\ParameterInterface */
        foreach ($package->getParameters() as $parameter) {
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
