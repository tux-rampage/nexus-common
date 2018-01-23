<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace Rampage\Nexus;

use Psr\Log\NullLogger;

/**
 * A logger that does nothing
 *
 * @deprecated Use \Psr\Log\NullLogger
 */
final class NoopLogger extends NullLogger
{
}
