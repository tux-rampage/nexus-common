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

namespace Rampage\Nexus\Repository;

/**
 * Defines the persistence manager
 */
interface PersistenceManagerInterface
{
    /**
     * Persist an entity
     *
     * @throws \InvalidArgumentException    When the provided object is not a persistable entity
     * @param object $object
     */
    public function persist($object);

    /**
     * @throws \InvalidArgumentException    When the provided object is not a persistable entity
     * @param object $object
     */
    public function remove($object);

    /**
     * @throws \RuntimeException    When flushing the changes fails
     * @param object $object
     */
    public function flush($object = null);
}