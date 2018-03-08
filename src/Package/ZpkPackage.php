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
use Rampage\Nexus\Exception\RuntimeException;
use SimpleXMLElement;
use Zend\Filter\Boolean as BoolFilter;
use Zend\Stdlib\Parameters;


/**
 * Implementation for ZendServer packages
 */
class ZpkPackage implements PackageInterface
{
    use ArrayExportableTrait;
    use BuildIdAwareTrait;
    use VersionStabilityTrait;

    const TYPE_ZPK = 'zpk';

    const ZPK_XML_NAMESPACE = 'http://www.zend.com/server/deployment-descriptor/1.0';

    const EXTRA_APP_DIR = 'app-dir';
    const EXTRA_SCRIPTS_DIR = 'scripts-dir';

    /**
     * @var PackageNameFilter
     */
    private $nameFilter;

    /**
     * @var SimpleXMLElement
     */
    protected $descriptor;

    /**
     * @var PackageParameter[]
     */
    protected $parameters = null;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var string
     */
    protected $archive;

    /**
     * @param SimpleXMLElement $descriptor
     */
    public function __construct(SimpleXMLElement $descriptor)
    {
        $this->nameFilter = new PackageNameFilter();
        $this->descriptor = $descriptor;
        $this->validate();

        $this->descriptor->registerXPathNamespace('zpk', self::ZPK_XML_NAMESPACE);
    }

    /**
     * @throws RuntimeException
     */
    private function validate()
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->descriptor->asXML());

        if (!$dom->schemaValidate(__DIR__ . '/../../resources/xsd/zpk.xsd')) {
            throw new RuntimeException('Invalid deployment descriptor');
        }
    }

    public function getId(): string
    {
        return $this->getName() . '@' . $this->getVersion();
    }

    public function getDocumentRoot(): string
    {
        return (string)$this->descriptor->docroot;
    }

    public function getExtra(string $name = null)
    {
        $extra = [
            self::EXTRA_APP_DIR => $this->getAppDir(),
            self::EXTRA_SCRIPTS_DIR => $this->getScriptsDir()
        ];

        if ($name !== null) {
            return (new Parameters($extra))->get($name);
        }

        return $extra;
    }

    public function getName(): string
    {
        return $this->nameFilter->filter($this->descriptor->name);
    }

    /**
     * Build package parameters
     */
    private function buildParameters()
    {
        $boolFilter = new BoolFilter(BoolFilter::TYPE_ALL);
        $this->parameters = [];

        foreach ($this->descriptor->xpath('./zpk:parameters/zpk:parameter') as $parameterXml) {
            $name = (string)$parameterXml['id'];
            $label = (string)$parameterXml['display']? : $name;
            $type = (string)$parameterXml['type'];
            $readonly = (string)$parameterXml['readonly'];
            $required = (string)$parameterXml['required'];
            $default = (string)$parameterXml->defaultvalue;

            if (!$name || !$type) {
                continue;
            }

            $required = $boolFilter($required);
            $readonly = $boolFilter($readonly);
            $param = new PackageParameter($name);

            $param->setType($type);
            $param->setRequired($required);
            $param->setDefault($default);
            $param->setLabel($label);
            $param->setOption('readonly', $readonly);

            switch ($type) {
                case 'choice':
                    $options = [];

                    $parameterXml->registerXPathNamespace('zpk', self::ZPK_XML_NAMESPACE);
                    foreach ($parameterXml->xpath('./zpk:validation/zpk:enums/zpk:enum') as $enum) {
                        $value = (string)$enum;
                        $options[$value] = $value;
                    }

                    $param->setType('select');
                    $param->setValueOptions($options);
                    break;
            }

            $this->parameters[] = $param;
        }
    }

    /**
     * @return iterable|PackageParameter[]
     */
    public function getParameters(): iterable
    {
        if ($this->parameters === null) {
            $this->buildParameters();
        }

        return $this->parameters;
    }

    public function getVariables(): array
    {
        if ($this->variables === null) {
            $this->variables = [];

            if (!isset($this->descriptor->variables->variable)) {
                return $this->variables;
            }

            foreach ($this->descriptor->variables->variable as $variable) {
                $key = (string)$variable['name'];

                if ($key == '') {
                    continue;
                }

                $this->variables[$key] = (string)$variable['value'];
            }
        }

        return $this->variables;
    }

    public function getType(): string
    {
        return self::TYPE_ZPK;
    }

    public function getVersion(): string
    {
        $version = (string)$this->descriptor->version->release;

        if (($version !== '') && $this->buildId) {
            $version .= '+' . $this->buildId;
        }

        return $version;
    }

    public function getAppDir(): string
    {
        return (string)$this->descriptor->appdir;
    }

    public function getScriptsDir(): string
    {
        return (string)$this->descriptor->scriptsdir;
    }
}
