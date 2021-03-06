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

namespace Rampage\Nexus\Archive\PackageLoader;

use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Package\ZpkPackage;

use PharData;
use Throwable;


/**
 * Implements the package loader for ZPK
 */
class ZpkLoader implements PackageLoaderInterface
{
    /**
     * The deployment descriptor filename
     */
    const DESCRIPTOR_FILE = 'deployment.xml';

    /**
     * @throws  InvalidArgumentException
     */
    private function read(PharData $archive): ZpkPackage
    {
        if (!$archive->offsetExists(static::DESCRIPTOR_FILE)) {
            throw new InvalidArgumentException('The Archive does not contain a deployment descriptor');
        }

        $xml = $archive[static::DESCRIPTOR_FILE]->getContent();
        return new ZpkPackage(new \SimpleXMLElement($xml));
    }

    public function canReadFromArchive(PharData $archive): bool
    {
        return $archive->offsetExists(static::DESCRIPTOR_FILE);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\PackageLoader\PackageLoaderInterface::load()
     */
    public function load(PharData $archive): PackageInterface
    {
        $package = $this->read($archive);
        $package->setArchive($archive->getPathname());

        return $package;
    }
}
