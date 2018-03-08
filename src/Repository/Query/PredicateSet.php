<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\Repository\Query;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

final class PredicateSet implements IteratorAggregate
{
    const TYPE_AND = 'and';
    const TYPE_OR = 'or';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $predicates = [];

    public function __construct($type = self::TYPE_AND)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function add(): void
    {

    }

    public function clear()
    {
        $this->predicates = [];
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->predicates);
    }
}