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
    protected $name = null;

    /**
     * Collection of VHosts
     *
     * @var VHost[]|ArrayCollection
     */
    protected $vhosts;

    /**
     * Collection of attached deployment nodes
     *
     * @var NodeInterface[]|ArrayCollection
     */
    protected $nodes;

    /**
     * Collection of applications for this target
     *
     * @var ApplicationInstance[]|ArrayCollection
     */
    protected $applications;

    /**
     * The aggregated state for this target
     *
     * @var string
     */
    protected $state = self::STATE_PENDING;

    /**
     * Maps the application status levels
     *
     * @var int[]
     */
    protected $statusAggregationLevels = [
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
    protected $workingStates = [
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * @param VHost $host
     * @throws LogicException
     * @return self
     */
    public function addVHost(VHost $host)
    {
        if ($this->hasVHostName($host->getName())) {
            throw new LogicException('Duplicate vhost name');
        }

        $this->vhosts[$host->getId()] = $host;
        return $this;
    }

    /**
     * @param string|null $id
     * @return \Rampage\Nexus\Entities\VHost
     */
    public function getVHost($id)
    {
        if ($id && isset($this->vhosts[$id])) {
            return $this->vhosts[$id];
        }

        return null;
    }

    /**
     * Check if there is a vhost with the given name
     *
     * @param string $name
     * @return boolean
     */
    public function hasVHostName($name)
    {
        return $this->vhosts->exists(function(VHost $item) use ($name) {
            return ($item->getName() == $name);
        });
    }

    /**
     * @return \Rampage\Nexus\Entities\VHost[]
     */
    public function getVHosts()
    {
        return $this->vhosts->toArray();
    }

    /**
     * @param VHost $host
     * @throws LogicException
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    public function removeVHost(VHost $host)
    {
        unset($this->vhosts[$host->getId()]);
        return $this;
    }

    /**
     * @return boolean
     */
    public function canManageVHosts()
    {
        // TODO: Implement actual check for managable vhost configs
        return true;
    }

    /**
     * @return \Rampage\Nexus\Entities\Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param string $state
     */
    protected function mapState($state)
    {
        if (isset($this->workingStates[$state])) {
            return self::STATE_WORKING;
        }

        return $state;
    }

    /**
     * Maps the state aggregation level
     *
     * @param string $state
     * @return int
     */
    protected function mapStateAggregationLevel($state)
    {
        if (isset($this->statusAggregationLevels[$state])) {
            return $this->statusAggregationLevels[$state];
        }

        return 0;
    }

    /**
     * Refresh the status
     */
    public function refreshStatus()
    {
        foreach ($this->nodes as $node) {
            $node->refresh();
        }

        $this->updateApplicationStates();
    }

    /**
     * Updates all application states from nodes
     */
    public function updateApplicationStates()
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
    public function aggregateState()
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
     *
     * @param string|ApplicationInstance $application
     * @return self
     */
    public function updateApplicationState($application)
    {
        if (!$application instanceof ApplicationInstance) {
            $application = $this->findApplication($application);

            if (!$application) {
                return $this;
            }
        }

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

        return $this;
    }

    /**
     * @param ApplicationInstance $application
     */
    public function addApplication(ApplicationInstance $application)
    {
        $id = (string)$application->getId();
        $this->applications[$id] = $application;
    }

    /**
     * @param unknown $id
     * @return NULL|\Rampage\Nexus\Entities\ApplicationInstance
     */
    public function findApplicationInstance($id)
    {
        $predicate = function(ApplicationInstance $item) use ($id) {
            return ($item->getId() == $id);
        };

        return $this->applications->filter($predicate)->first();
    }

    /**
     * @param Application $application
     * @return ApplicationInstance[]
     */
    public function findInstanceByApplication(Application $application)
    {
        $predicate = function(ApplicationInstance $instance) use ($application) {
            return ($instance->getApplication()->getId() == $application->getId());
        };

        return $this->applications->filter($predicate)->toArray();
    }

    /**
     * @return \Rampage\Nexus\Entities\ApplicationInstance[]
     */
    public function getApplications()
    {
        return $this->applications->toArray();
    }

    /**
     * @param ApplicationInstance $instance
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    public function removeApplication(ApplicationInstance $instance)
    {
        $instance->remove();
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        if (!in_array($state, [self::STATE_ERROR, self::STATE_PENDING, self::STATE_WORKING])) {
            throw new InvalidArgumentException('Invalid deploy target state: ' . $state);
        }

        $this->state = $state;
        return $this;
    }

    /**
     * @return boolean
     */
    public function canSync()
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
    public function sync()
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

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);
        $this->name = $data->get('name');
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'canManageVHosts' => $this->canManageVHosts(),
            'vhosts' => [],
            'nodes' => [],
            'applications' => [],
        ];

        foreach ($this->vhosts as $vhost) {
            $array['vhosts'][] = $vhost->toArray();
        }

        foreach ($this->nodes as $node) {
            $array['nodes'][] = $node->toArray();
        }

        foreach ($this->applications as $application) {
            $array['applications'][] = $application->toArray();
        }

        return $array;
    }
}
