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

namespace Rampage\Nexus\Repository\RestService;

use Psr\Http\Message\ServerRequestInterface;
use Rampage\Nexus\Exception\Http\BadRequestException;

trait PostableTrait
{
    use RepositoryTrait;

    /**
     * Create a new entity from data
     *
     * @param array $data
     * @return object|null
     */
    abstract private function createNewEntity(array $data);

    /**
     * @param ServerRequestInterface $request
     * @return object|null
     */
    function post(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        if (!is_array($data)) {
            throw new BadRequestException('Invalid request body', BadRequestException::UNPROCESSABLE);
        }

        if (null !== ($entity = $this->createNewEntity($data))) {
            $this->persistenceManager->persist($entity);
            $this->persistenceManager->flush($entity);
        }

        return $entity;
    }
}