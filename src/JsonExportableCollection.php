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

namespace Rampage\Nexus;

use ArrayObject;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Rampage\Nexus\Entities\Api\ArrayExportableInterface;
use Rampage\Nexus\Exception\UnexpectedValueException;
use function is_array;

/**
 * Builds a JSON serializable collection
 */
class JsonExportableCollection implements JsonSerializable, IteratorAggregate
{
    /**
     * @var iterable
     */
    private $collection;

    public function __construct(iterable $collection)
    {
        $this->collection = $collection;
    }

    public function getIterator(): Iterator
    {
        foreach ($this->collection as $key => $item) {
            yield $key => $item;
        }
    }

    private function acceptExportedItem($value): bool
    {
        return (is_array($value) || ($value instanceof ArrayObject) || ($value instanceof JsonSerializable));
    }

    public function jsonSerialize(): array
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
            if (!$this->acceptExportedItem($item)) {
                throw new UnexpectedValueException(sprintf('Expected collection item to be an array, ArrayObject or implement JsonSerializable. Got %s', is_object($item)? get_class($item) : gettype($item)));
            }

            $result['items'][] = $item;
            $result['count']++;
        }

        return $result;
    }
}