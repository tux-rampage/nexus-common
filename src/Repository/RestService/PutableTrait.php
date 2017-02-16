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

/**
 * Repo put implementation
 */
trait PutableTrait
{
    use RepositoryTrait;

    /**
     * @return static
     */
    abstract private function updateEntity($entity, array $data);

    /**
     * @param int|string $id
     * @param array $data
     * @return object|null
     */
    private function putEntity($id, array $data)
    {
        if (null !== ($entity = $this->repository->findOne($id))) {
            $this->updateEntity($entity, $data);
            $this->repository->save($entity);
        }

        return $entity;

    }

    /**
     * @param ServerRequestInterface $request
     * @throws BadRequestException
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    private function getPutItemsList(ServerRequestInterface $request)
    {
        $request->getParsedBody();

        if (!isset($request['items']) && !is_array($request['items'])) {
            throw new BadRequestException('Invalid request body', BadRequestException::UNPROCESSABLE);
        }

        return $request['items'];
    }

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function put(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');

        if ($id) {
            return $this->putEntity($id, $request->getParsedBody());
        }

        $items = $this->getPutItemsList($request);
        $results = [];

        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'])) {
                $results[] = false;
                continue;
            }

            $results[] = $this->putEntity($item['id'], $item)? : false;
        }

        return compact('results');
    }
}