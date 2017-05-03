<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitterTrait;


/**
 * SAPI Stream emitter
 */
final class SapiStreamEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    /**
     * @param number $toLevel
     */
    private function clearBuffers($toLevel = 0)
    {
        if ($toLevel < 0) {
            $toLevel = 0;
        }

        while (ob_get_level() > $toLevel) {
            ob_end_clean();
        }
    }

    /**
     * @param StreamInterface $body
     * @return bool
     */
    private function acceptBody($body)
    {
        if (!$body instanceof StreamInterface) {
            return false;
        }

        return (
            $body->isReadable() &&
            $body->isSeekable() &&
            ($body->getMetadata('stream_type') === null)
        );
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Diactoros\Response\EmitterInterface::emit()
     */
    public function emit(ResponseInterface $response)
    {
        $body = $response->getBody();

        if (headers_sent() || !$this->acceptBody($body)) {
            return false;
        }

        $body->rewind();
        $stream = $body->detach();

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->clearBuffers();

        if (is_resource($stream)) {
            fpassthru($stream);
        }

        return true;
    }
}
