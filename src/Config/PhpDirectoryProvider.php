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

namespace Rampage\Nexus\Config;

/**
 * Implements a config provider for include directories that will also work within Phars
 */
class PhpDirectoryProvider
{
    private $directory;

    private $suffix;

    /**
     * @param string $directory
     * @param string $suffix
     */
    public function __construct($directory, $suffix = '.conf.php')
    {
        $this->directory = $directory;
        $this->suffix = $suffix;
    }

    /**
     * @return \CallbackFilterIterator|\SplFileInfo[]
     */
    private function getIterator()
    {
        $iterator = new \FilesystemIterator($this->directory);
        $suffixLength = strlen($this->suffix);

        return new \CallbackFilterIterator($iterator, function(\SplFileInfo $current, $key, $iterator) use ($suffixLength) {
            return (substr($current->getFilename(), 0 - $suffixLength) == $this->suffix);
        });
    }

    /**
     * @return Generator
     */
    public function __invoke()
    {
        foreach ($this->getIterator() as $file) {
            yield include $file->getPathname();
        }
    }
}
