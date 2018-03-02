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

use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Deployment\NodeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\InvalidArgumentException;

/**
 * Persistable deploy target
 */
class DeployTarget
{
    const STATE_PENDING = 'pending';
    const STATE_WORKING = 'working';
    const STATE_ERROR = 'error';

    /**
     * Target identifier
     *
     * @var string
     */
    private $id = null;

    /**
     * Displayable name
     *
     * @var string
     */
    private $name = null;

    /**
     * Collection of VHosts
     *
     * @var VHost[]|ArrayCollection
     */
    private $vhosts;

    /**
     * Collection of attached deployment nodes
     *
     * @var NodeInterface[]|ArrayCollection
     */
    private $nodes;

    /**
     * Collection of applications for this target
     *
     * @var ApplicationInstance[]|ArrayCollection
     */
    private $applications;

    /**
     * The aggregated state for this target
     *
     * @var string
     */
    private $state = self::STATE_PENDING;

    /**
     * Maps the application status levels
     *
     * @var int[]
     */
    private $statusAggregationLevels = [
        ApplicationInstance::STATE_PENDING => 1,
        ApplicationInstance::STATE_REMOVED => 2,
        ApplicationInstance::STATE_INACTIVE => 4,
        ApplicationInstance::STATE_DEPLOYED => 8,
        ApplicationInstance::STATE_ERROR => 16,
        self::STATE_WORKING => 32,
    ];

    /**
     * Maps working states
     *
     * @var string[]
     */
    private $workingStates = [
        ApplicationInstance::STATE_ACTIVATING,
        ApplicationInstance::STATE_DEACTIVATING,
        ApplicationInstance::STATE_REMOVING,
        ApplicationInstance::STATE_STAGING,
    ];

    /**
     * @param string $type
     */
    public function __construct()
    {
        $this->nodes = new ArrayCollection();
        $this->vhosts = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @throws LogicException
     */
    public function addVHost(VHost $host): void
    {
        if ($this->hasVHostName($host->getName())) {
            throw new LogicException('Duplicate vhost name');
        }

        $this->vhosts[$host->getId()] = $host;
    }

    public function getVHost($id): ?VHost
    {
        if ($id && isset($this->vhosts[$id])) {
            return $this->vhosts[$id];
        }

        return null;
    }

    /**
     * Check if there is a vhost with the given name
     */
    public function hasVHostName(string $name): bool
    {
        return $this->vhosts->exists(function(VHost $item) use ($name) {
            return ($item->getName() == $name);
        });
    }

    /**
     * @return VHost[]
     */
    public function getVHosts(): iterable
    {
        return $this->vhosts->toArray();
    }

    /**
     * @throws LogicException
     */
    public function removeVHost(VHost $host): void
    {
        unset($this->vhosts[$host->getId()]);
    }

    public function canManageVHosts(): bool
    {
        return true;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): iterable
    {
        return $this->nodes;
    }

    private function mapState(string $state): string
    {
        if (isset($this->workingStates[$state])) {
            return self::STATE_WORKING;
        }

        return $state;
    }

    private function mapStateAggregationLevel(string $state): int
    {
        if (isset($this->statusAggregationLevels[$state])) {
            return $this->statusAggregationLevels[$state];
        }

        return 0;
    }

    /**
     * Refresh the status
     */
    public function refreshStatus(): void
    {
        foreach ($this->nodes as $node) {
            $node->refresh();
        }

        $this->updateApplicationStates();
    }

    /**
     * Updates all application states from nodes
     */
    public function updateApplicationStates(): void
    {
        foreach ($this->applications as $application) {
            $this->updateApplicationState($application);
        }

        $this->aggregateState();
    }

    /**
     * Aggregates the state from all applications
     *
     * @return string
     */
    public function aggregateState(): string
    {
        $this->state = self::STATE_PENDING;

        foreach ($this->applications as $application) {
            $state = $application->getState();

            // Any working node causes the state working
            if ($state == self::STATE_WORKING) {
                $this->state = self::STATE_WORKING;
                break;
            } else if ($state == ApplicationInstance::STATE_ERROR) {
                $this->state = self::STATE_ERROR;
            }
        }

        return $this->state;
    }

    /**
     * Update the state of a single application
     */
    public function updateApplicationState(ApplicationInstance $application): void
    {
        $state = ApplicationInstance::STATE_UNKNOWN;
        $level = 0;

        foreach ($this->nodes as $node) {
            $nodeState = $node->getApplicationState($application);
            $mappedState = $this->mapState($nodeState);
            $nodeLevel = $this->mapStateAggregationLevel($mappedState);

            if ($level < $nodeLevel) {
                $state = $nodeState;
                $level = $nodeLevel;
            }
        }

        $application->setState($state);

        if ($application->isRemoved() && ($application->getState() == ApplicationInstance::STATE_REMOVED)) {
            $key = (string)$application->getId();
            unset($this->applications[$key]);
        }
    }

    public function addApplication(ApplicationInstance $application): void
    {
        $id = (string)$application->getId();
        $this->applications[$id] = $application;
    }

    public function findApplicationInstance(string $id): ?ApplicationInstance
    {
        $predicate = function(ApplicationInstance $item) use ($id) {
            return ($item->getId() == $id);
        };

        return $this->applications->filter($predicate)->first();
    }

    /**
     * @return ApplicationInstance[]
     */
    public function findInstanceByApplication(Application $application): iterable
    {
        $predicate = function(ApplicationInstance $instance) use ($application) {
            return ($instance->getApplication()->getId() == $application->getId());
        };

        return $this->applications->filter($predicate)->toArray();
    }

    /**
     * @return ApplicationInstance[]
     */
    public function getApplications(): iterable
    {
        return $this->applications->toArray();
    }

    public function removeApplication(ApplicationInstance $instance): void
    {
        $instance->remove();
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        if (!in_array($state, [self::STATE_ERROR, self::STATE_PENDING, self::STATE_WORKING])) {
            throw new InvalidArgumentException('Invalid deploy target state: ' . $state);
        }

        $this->state = $state;
    }

    public function canSync(): bool
    {
        foreach ($this->nodes as $node) {
            if ($node->canSync()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws LogicException
     */
    public function sync(): void
    {
        if (!$this->canSync()) {
            throw new LogicException('Target is not syncable');
        }

        foreach ($this->nodes as $node) {
            if ($node->canSync()) {
                $node->sync();
            }
        }
    }
}
