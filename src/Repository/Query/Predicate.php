<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\Repository\Query;

final class Predicate
{
    const OP_EQUALS = '=';
    const OP_GT = '>';
    const OP_LT = '<';
    const OP_IN = 'in';
    const OP_GTE = '>=';
    const OP_LTE = '<=';

    public function __construct(string $property, $value, string $operand = '=')
    {

    }

}