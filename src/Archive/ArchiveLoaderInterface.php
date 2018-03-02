<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus\Archive;

use Rampage\Nexus\Exception\RuntimeException;

use Rampage\Nexus\Package\PackageInterface;
use SplFileInfo;
use PharData;

/**
 * Interface for loading archive files
 */
interface ArchiveLoaderInterface
{
    /**
     * Returns the download directory
     *
     * @return string
     */
    public function getDownloadDirectory();

    /**
     * Adds a downloader implementation
     *
     * @param DownloaderInterface $downloader
     */
    public function addDownloader(DownloaderInterface $downloader);

    /**
     * Ensures the archive is available locally
     *
     * @param   string      $archive    The path or URI to the archive
     * @return  SplFileInfo             The file info instance for the local archive file
     * @throws  RuntimeException        When the file cannot be provided locally
     */
    public function ensureLocalArchiveFile($archive);

    /**
     * Returns the package from the given archive
     *
     * @param   PharData            $archive    The archive instance
     * @return  PackageInterface                The resulting package
     * @throws  RuntimeException                When the packagetype is not available
     */
    public function getPackage(PharData $archive): PackageInterface;
}
