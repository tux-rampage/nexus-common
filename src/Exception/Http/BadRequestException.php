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

namespace Rampage\Nexus\Exception\Http;

use Rampage\Nexus\Exception\RuntimeException;


/**
 * Exception for bad requests
 */
class BadRequestException extends RuntimeException implements ExceptionInterface
{
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const NOT_ALLOWED = 405;
    const UNPROCESSABLE = 422;
    const CONFLICT = 409;
    const ENTITY_EXISTS = 409;

    public function __construct($message, $code = self::BAD_REQUEST, $previous = null)
    {
        if ($code == 0) {
            $code = self::BAD_REQUEST;
        }

        parent::__construct($message, $code, $previous);
    }
}
