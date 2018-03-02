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

namespace Rampage\Nexus\Entities;

use phpDocumentor\Reflection\Types\Void_;
use Rampage\Nexus\Deployment\NodeInterface;
use Rampage\Nexus\Deployment\NodeStrategyInterface;
use Rampage\Nexus\Deployment\NodeStrategyProviderInterface;

use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Rampage\Nexus\Exception\LogicException;

use Zend\Stdlib\Parameters;
use Traversable;


/**
 * Implements the default node entity
 */
class Node implements NodeInterface
{
    /**
     * @var string
     */
    private $id = null;

    /**
     * Human readable node name
     *
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var DeployTarget
     */
    protected $deployTarget = null;

    /**
     * The node's communication url
     *
     * @var string
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $state = self::STATE_UNINITIALIZED;

    /**
     * The current application states indexed by app id
     *
     * @var string[]
     */
    protected $applicationStates = [];

    /**
     * @var string
     */
    protected $secret = null;

    /**
     * @var array
     */
    protected $serverInfo = [];

    /**
     * @var array
     */
    private $flatServerInfo = null;

    /**
     * @var NodeStrategyProviderInterface
     */
    private $strategyProvider = null;

    /**
     * @var NodeStrategyInterface
     */
    private $strategy = null;

    public function __construct(string $type)
    {
        if (!$type) {
            throw new LogicException('The node type must not be empty');
        }

        $this->type = $type;
    }

    /**
     * Sets the node's general state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Update application states
     */
    public function updateApplicationStates(iterable $states): void
    {
        foreach ($states as $appId => $state) {
            $this->applicationStates[$appId] = (string)$state;
        }
    }

    /**
     * Set the application states
     */
    public function setApplicationStates(iterable $states): void
    {
        $this->applicationStates = [];
        $this->updateApplicationStates($states);
    }

    public function setStrategyProvider(NodeStrategyProviderInterface $provider): void
    {
        $this->strategyProvider = $provider;
    }

    /**
     * Sets the node strategy
     */
    public function setStrategy(NodeStrategyInterface $strategy): void
    {
        $strategy->setEntity($this);
        $this->strategy = $strategy;
    }

    /**
     * @throws LogicException
     */
    public function getStrategy(): NodeStrategyInterface
    {
        if (!$this->strategy) {
            if (!$this->strategyProvider || !$this->strategyProvider->has($this->type)) {
                throw new LogicException('Missing node strategy');
            }

            $this->setStrategy($this->strategyProvider->get($this->type));
        }

        return $this->strategy;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function updateState(string $state, iterable $applicationStates = null): void
    {
        $this->state = (string)$state;
        $this->setApplicationStates($applicationStates ?? []);
    }

    public function isAttached(): bool
    {
        return ($this->deployTarget !== null);
    }

    /**
     * Returns the deploy target the node is attached to
     */
    public function getDeployTarget(): DeployTarget
    {
        return $this->deployTarget;
    }

    public function attach(DeployTarget $deployTarget): void
    {
        if ($this->deployTarget) {
            throw new LogicException('This node is already attached to a deploy target');
        }

        $this->deployTarget = $deployTarget;
        $this->getStrategy()->attach($deployTarget);
    }

    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return mixed The requested server info
     */
    public function getServerInfo(string $key = null)
    {
        if ($key === null) {
            return $this->serverInfo;
        }

        if ($this->flatServerInfo === null) {
            $this->flatServerInfo = [];

            foreach ($this->serverInfo as $key => $value) {
                $this->flatServerInfo[$key] = $value;

                if (is_iterable($value)) {
                    $this->flattenCollection($value, $key, $this->serverInfo);
                }
            }
        }

        if (!isset($this->flatServerInfo[$key])) {
            return null;
        }

        return $this->flatServerInfo[$key];
    }

    /**
     * Flattens an iterable into the context array
     */
    private function flattenCollection(iterable $values, string $prefix, array &$context): void
    {
        foreach ($values as $key => $value) {
            $flattenedKey = $prefix . '.' . $key;
            $context[$flattenedKey] = $value;
            $this->flattenCollection($value, $flattenedKey, $context);
        }
    }

    /**
     * Sets the server info
     *
     * Nested array values will be flattened to dot-concatenated keys
     * while the original array will stay in place
     */
    public function setServerInfo(array $serverInfo): void
    {
        $this->serverInfo = $serverInfo;
        $this->flatServerInfo = null;
    }

    public function acceptsClusterSibling(NodeInterface $node): bool
    {
        return $this->getStrategy()->acceptsClusterSibling($node);
    }

    public function detach(): void
    {
        $this->getStrategy()->detach();
        $this->deployTarget = null;
    }

    public function rebuild(ApplicationInstance $application = null): void
    {
        $this->getStrategy()->rebuild($application);
    }

    public function refresh(): void
    {
        $this->getStrategy()->refresh();
    }

    public function sync(): void
    {
        $this->getStrategy()->sync();
    }

    public function getTypeId(): string
    {
        return $this->getStrategy()->getTypeId();
    }

    public function getApplicationState(ApplicationInstance $application): string
    {
        if (!isset($this->applicationStates[$application->getId()])) {
            return ApplicationInstance::STATE_UNKNOWN;
        }

        return $this->applicationStates[$application->getId()];
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function canSync(): bool
    {
        return $this->getStrategy()->canSync();
    }
}
