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

namespace Rampage\Nexus\Package\Constraint;

/**
 * Constraint combination
 */
class ConstraintCombination implements ConstraintInterface
{
    const TYPE_AND = 'and';
    const TYPE_OR  = 'or';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ConstraintInterface[]
     */
    protected $constraints;

    /**
     * @param iterable|ConstraintInterface[] $constraints
     */
    public function __construct(iterable $constraints = [], $type = self::TYPE_AND)
    {
        $this->type = $type;
        $this->constraints = array();

        foreach ($constraints as $constraint) {
            $this->add($constraint);
        }
    }

    /**
     * @param ConstraintInterface $constraint
     */
    public function add(ConstraintInterface $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $version): bool
    {
        if ($this->type == self::TYPE_AND) {
            foreach ($this->constraints as $constraint) {
                if (!$constraint->match($version)) {
                    return false;
                }
            }

            return (count($this->constraints) > 0);
        }

        // OR

        foreach ($this->constraints as $constraint) {
            if ($constraint->match($version)) {
                return true;
            }
        }

        return false;
    }
}
