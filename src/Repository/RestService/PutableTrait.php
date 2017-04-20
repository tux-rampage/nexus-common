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
use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;

/**
 * Repo put implementation
 */
trait PutableTrait
{
    use RepositoryTrait;

    /**
     * @param ArrayExchangeInterface $entity
     * @param array $data
     * @return static
     */
    private function updateEntity($entity, array $data)
    {
        $entity->exchangeArray($data);
        return $this;
    }

    /**
     * @param int|string $id
     * @param array $data
     * @return object|null
     */
    private function putEntity($id, array $data)
    {
        if (null !== ($entity = $this->repository->findOne($id))) {
            $this->updateEntity($entity, $data);
            $this->persistenceManager->persist($entity);
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
        $result = null;

        if ($id) {
            $result = $this->putEntity($id, $request->getParsedBody());
        } else {

            $items = $this->getPutItemsList($request);
            $results = [];

            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['id'])) {
                    $results[] = false;
                    continue;
                }

                $results[] = $this->putEntity($item['id'], $item)? : false;
            }

            $result = compact('results');
        }

        $this->persistenceManager->flush();
        return $result;
    }
}