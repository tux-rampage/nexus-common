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

namespace Rampage\Nexus;

use RecursiveDirectoryIterator;
use DirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Filesystem API
 */
class FileSystem implements FileSystemInterface
{
    public function ensureDirectory(string $dir, int $mode = null): void
    {
        if (!is_dir($dir) && !mkdir($dir, $mode? : 0755, true)) {
            throw new Exception\RuntimeException(sprintf('Failed to create directory: "%s"', $dir));
        }
    }

    public function delete(string $fileOrDirectory): bool
    {
        if (!is_dir($fileOrDirectory) || is_link($fileOrDirectory)) {
            return unlink($fileOrDirectory);
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fileOrDirectory), RecursiveIteratorIterator::CHILD_FIRST);

        /* @var $fileInfo \SplFileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isLink()) {
                if (!rmdir($fileInfo->getPathname())) {
                    return false;
                }

                continue;
            }

            if (!unlink($fileInfo->getPathname())) {
                return false;
            }
        }

        return true;
    }

    public function purgeDirectory(string $dir): void
    {
        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            $this->delete($file->getPathname());
        }
    }
}
