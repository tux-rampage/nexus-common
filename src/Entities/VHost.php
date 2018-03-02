<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\Entities;

use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\UnexpectedValueException;

/**
 * Vhost definition
 */
class VHost
{
    /**
     * The name for the default vhost
     */
    const DEFAULT_VHOST = '*';

    /**
     * Regex for valid server names
     */
    const VALID_NAME_REGEX = '~^[a-z0-9_-]+(\.[a-z0-9_-]+)*$~';

    /**
     * The VHost identifier
     *
     * Once persisted this must be considerd immutable
     *
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * The default flavour for this host
     *
     * @var string
     */
    protected $flavor = null;

    /**
     * Contains aliases for this vhost
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * Flag if SSL should be enabled
     *
     * The result depends on the corresponding flavor.
     *
     * @var bool
     */
    protected $enableSsl = false;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->id = sha1(uniqid('VHOST.', true));
        $this->setName($name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Checks if this is the default vhost
     */
    public function isDefault(): bool
    {
        return ($this->name == self::DEFAULT_VHOST);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        if ($name == '') {
            throw new UnexpectedValueException('The VHost name must not be empty');
        }

        if (!preg_match(self::VALID_NAME_REGEX, $name) || ($name == self::DEFAULT_VHOST)) {
            throw new UnexpectedValueException(sprintf('Invalid vhost name: "%s"', $name));
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = ($flavor !== null)? (string)$flavor : null;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param multitype:\Rampage\Nexus\Entities\string  $aliases
     * @return self
     */
    public function setAliases($aliases)
    {
        $this->clearAliases();

        foreach ($aliases as $alias) {
            $this->addAlias($alias);
        }

        return $this;
    }

    /**
     * Remove all aliases for this vhost
     *
     * @return self
     */
    public function clearAliases()
    {
        $this->aliases = [];
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function addAlias($name)
    {
        $name = (string)$name;

        if ($name && !in_array($name, $this->aliases)) {
            $this->aliases[] = $name;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function removeAlias($name)
    {
        $offset = array_search($name, $this->aliases);

        if ($offset !== false) {
            unset($this->aliases[$offset]);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSslEnabled()
    {
        return $this->enableSsl;
    }

    /**
     * @param boolean $enableSsl
     * @return self
     */
    public function setEnableSsl($enableSsl)
    {
        $this->enableSsl = (bool)$enableSsl;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);

        $this->setName($data->get('name'));
        $this->setFlavor($data->get('flavor'));
        $this->setAliases($data->get('aliases'));
        $this->setEnableSsl($data->get('enableSsl'));
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        return [
            'id' => $this->isDefault()? null : $this->id,
            'name' => $this->name,
            'isDefault' => $this->isDefault(),
            'flavor' => $this->flavor,
            'aliases' => $this->aliases,
            'enableSsl' => $this->enableSsl
        ];
    }
}
