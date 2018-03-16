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

namespace Rampage\Nexus;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger as SymfonyConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Decorates a logger and tees to the console
 */
class ConsoleLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SymfonyConsoleLogger
     */
    private $consoleLogger;

    public function __construct(OutputInterface $output, LoggerInterface $logger = null)
    {
        $this->logger = $logger?: new NullLogger();
        $this->consoleLogger = new SymfonyConsoleLogger($output);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
        $this->consoleLogger->log($level, $message, $context);
    }
}
