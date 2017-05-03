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

use Psr\Http\Message\StreamInterface;


/**
 * Implements a noop stream like /dev/null
 */
final class NullStream implements StreamInterface
{
    /**
     * @var array
     */
    static private $meta = [
        'timed_out' => false,
        'blocked' => false,
        'eof' => true,
        'mode' =>  'r',
        'seekable' => false
    ];

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::__toString()
     */
    public function __toString()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::close()
     */
    public function close()
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::detach()
     */
    public function detach()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::eof()
     */
    public function eof()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::getContents()
     */
    public function getContents()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::getMetadata()
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return self::$meta;
        }

        return (isset(self::$meta[$key]))? self::$meta[$key] : null;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::getSize()
     */
    public function getSize()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::isReadable()
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::isSeekable()
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::isWritable()
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::read()
     */
    public function read($length)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::rewind()
     */
    public function rewind()
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::seek()
     */
    public function seek($offset, $whence = SEEK_SET)
    {
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::tell()
     */
    public function tell()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Message\StreamInterface::write()
     */
    public function write($string)
    {
    }
}
