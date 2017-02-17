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

namespace Rampage\Nexus\Api;

use Rampage\Nexus\Entities\Api\ArrayExportableInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Exception\UnexpectedValueException;

use Zend\Stdlib\ArraySerializableInterface;

use ArrayObject;
use ArrayIterator;
use JsonSerializable;
use Traversable;
use Iterator;
use IteratorAggregate;
use IteratorIterator;


/**
 * Builds a JSON serializable collection
 */
class JsonExportableCollection implements JsonSerializable, IteratorAggregate
{
    /**
     * @var array|Traversable|ArrayExportableInterface
     */
    private $collection;

    /**
     * @param array|Traversable|ArrayExportableInterface $collection
     */
    public function __construct($collection)
    {
        if (($collection instanceof ArrayExportableInterface)
            && !is_array($collection)
            && !($collection instanceof Traversable)) {

            throw new InvalidArgumentException(sprintf('The provided collection must be an array or implement the Traversable interface, %s given', is_object($collection)? get_class($collection) : gettype($collection)));
        }

        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        if (is_array($this->collection)) {
            $iterator = new ArrayIterator($this->collection);
        } else if ($this->collection instanceof ArrayExportableInterface) {
            $iterator = new ArrayIterator($this->collection->toArray());
        } else {
            $iterator = ($this->collection instanceof Iterator)? $this->collection : new IteratorIterator($this->collection);
        }

        return $iterator;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    protected function exportCollectionItemToArray($item)
    {
        if ($item instanceof ArrayExportableInterface) {
            $item = $item->toArray();
        } else if ($item instanceof ArraySerializableInterface) {
            $item = $item->getArrayCopy();
        }

        return $item;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    private function acceptExportedItem($value)
    {
        return (is_array($value) || ($value instanceof ArrayObject) || ($value instanceof JsonSerializable));
    }

    /**
     * {@inheritDoc}
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        $collection = $this->collection;

        if ($collection instanceof ArrayExportableInterface) {
            return $collection->toArray();
        }

        $result = [
            'count' => 0,
            'items' => []
        ];

        foreach ($collection as $item) {
            $item = $this->exportCollectionItemToArray($item);

            if (!$this->acceptExportedItem($item)) {
                throw new UnexpectedValueException(sprintf('Expected collection item to be an array or array representative. Got %s', is_object($item)? get_class($item) : gettype($item)));
            }

            $result['items'][] = $item;
            $result['count']++;
        }

        return $result;
    }
}