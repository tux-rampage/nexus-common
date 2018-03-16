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

namespace Rampage\Nexus\Package;

use Rampage\Nexus\Entities\PackageParameter;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Zend\Stdlib\Parameters;


/**
 * Implementation for composer packages
 */
class ComposerPackage implements PackageInterface
{
    use BuildIdAwareTrait;
    use VersionStabilityTrait;

    /**
     * Composer package type constant
     */
    const TYPE_COMPOSER = 'composer';

    /**
     * @var PackageNameFilter
     */
    private $nameFilter;

    /**
     * Deployment section within composer.json
     *
     * @var Parameters
     */
    protected $data;

    /**
     * Content of composer.json
     *
     * @var Parameters
     */
    protected $composer;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;


    public function __construct(string $json)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        $this->nameFilter = new PackageNameFilter();
        $this->composer = new Parameters($json);
        $this->validate();
        $this->data = new Parameters($this->composer['extra']['deployment']);
    }

    /**
     * @throws UnexpectedValueException
     */
    private function validate(): void
    {
        $requiredFields = ['name', 'version'];

        foreach ($requiredFields as $field) {
            if (!isset($this->composer[$field]) || ($this->composer[$field] == '')) {
                throw new UnexpectedValueException('Missing field in composer.json: ' . $field);
            }
        }

        if (!isset($this->composer['extra']['deployment']) || !is_array($this->composer['extra']['deployment'])) {
            throw new UnexpectedValueException('Missing deployment section in composer.json');
        }
    }

    public function getId(): string
    {
        return $this->getName() . '@' . $this->getVersion();
    }

    public function getDocumentRoot(): string
    {
        return $this->data->get('docroot');
    }

    public function getExtra(string $name = null)
    {
        $extra = $this->composer->get('extra', []);
        unset($extra['deployment']);

        return $extra;
    }

    public function getName(): string
    {
        return $this->nameFilter->filter($this->composer->get('name'));
    }

    private function buildParameters(): void
    {
        $this->parameters = [];

        if (!isset($this->data['parameters'])
            || !is_array($this->data['parameters'])) {
            return;
        }

        foreach ($this->data['parameters'] as $name => $param) {
            if (!is_string($name) || ($name == '')) {
                continue;
            }

            $parameter = new PackageParameter($name);

            if (is_string($param)) {
                $parameter->setType($param);
            } else {
                $parameter->exchangeArray($param);
            }

            $this->parameters[$parameter->getName()] = $parameter;
        }
    }

    public function getParameters(): array
    {
        if ($this->parameters === null) {
            $this->buildParameters();
        }

        return $this->parameters;
    }

    public function getVariables(): array
    {
        return $this->data['variables'] ?? [];
    }

    public function getType(): string
    {
        return self::TYPE_COMPOSER;
    }

    public function getVersion(): string
    {
        $version = $this->composer->get('version');

        if ($this->buildId !== null) {
            $version .= '+' . $this->buildId;
        }

        return $version;
    }

    public function jsonSerialize(): array
    {
        return (new JsonSerializer())->extract($this);
    }
}
