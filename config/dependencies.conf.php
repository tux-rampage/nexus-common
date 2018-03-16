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

use Symfony\Component\Console\Application as ConsoleApplication;
use Zend\Crypt\PublicKey\Rsa\PrivateKey;
use Zend\Crypt\Password\PasswordInterface;
use Zend\Crypt\Password\Bcrypt as BcryptPasswordStrategy;
use Zend\Diactoros\Response\EmitterInterface;


return [
    'dependencies' => [
        'factories' => [
            EmitterInterface::class => ServiceFactory\ResponseEmitterFactory::class,
            ConsoleApplication::class => ServiceFactory\ConsoleApplicationFactory::class,
            PrivateKey::class => ServiceFactory\PrivateKeyFactory::class,
            Archive\ArchiveLoader::class => ServiceFactory\ArchiveLoaderFactory::class,
            'RuntimeConfig' => ServiceFactory\RuntimeConfigFactory::class,
        ],

        'aliases' => [
            FileSystemInterface::class => FileSystem::class,
            Archive\ArchiveLoaderInterface::class => Archive\ArchiveLoader::class,

            ApiClient\RequestSignatureInterface::class => ApiClient\PublicKeySignature::class,
            ApiClient\AuthenticateStrategy::class => ApiClient\PublicKeySignature::class,

            PasswordInterface::class => BcryptPasswordStrategy::class,
            Config\PropertyConfigInterface::class => 'RuntimeConfig',
        ],

        'instances' => [
            'RuntimeConfig' => [ 'aliasOf' => Config\ArrayConfig::class ],
        ],
    ],
];
