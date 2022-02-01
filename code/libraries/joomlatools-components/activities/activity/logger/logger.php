<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Logger.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesActivityLogger extends KObject implements ComActivitiesActivityLoggerInterface
{
    /**
     * List of actions to log.
     *
     * @var array
     */
    protected $_actions;

    /**
     * The name of the column to use as the title column in the activity entry.
     *
     * @var string
     */
    protected $_title_column;

    /**
     * Activity controller identifier.
     *
     * @param string|KObjectIdentifierInterface
     */
    protected $_controller;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_title_column = KObjectConfig::unbox($config->title_column);
        $this->_controller   = $config->controller;

        $this->setActions(KObjectConfig::unbox($config->actions));
    }

    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'actions'      => array('after.edit', 'after.add', 'after.delete'),
            'title_column' => array('title', 'name'),
            'controller'   => 'com:activities.controller.activity'
        ));

        parent::_initialize($config);
    }

    /**
     * Log an activity.
     *
     * @param string                     $action  The action to log.
     * @param KModelEntityInterface      $object  The activity object on which the action is performed.
     * @param KObjectIdentifierInterface $subject The activity subject who is performing the action.
     */
    public function log($action, KModelEntityInterface $object, KObjectIdentifierInterface $subject)
    {
        $controller = $this->_getController();

        if($controller instanceof KControllerModellable)
        {
            foreach($object as $entity)
            {
                // Only log if the entity status is valid.
                $status = $this->getActivityStatus($entity, $action);

                if (!empty($status) && $status !== KModelEntityInterface::STATUS_FAILED)
                {
                    // Get the activity data
                    $data = $this->getActivityData($entity, $subject, $action);

                    // Set the status
                    if(!isset($data['status'] )) {
                        $data['status'] = $status;
                    }

                    // Set the action
                    if(!isset($data['action']))
                    {
                        $parts = explode('.', $action);
                        $data['action'] = $parts[1];
                    }

                    $controller->add($data);
                }
            }
        }
    }

    /**
     * Activity controller getter
     *
     * @param array $config An optional configuration array for the controller.
     *
     *  @return KControllerInterface The activity controller
     */
    protected function _getController($config = array())
    {
        return $this->getObject($this->_controller, $config);
    }

    /**
     * Return the list of actions the logger should listen to for logging.
     *
     * @return array The list of actions.
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Set the list of actions the logger should listen to for logging.
     *
     * @param array $actions The list of actions.
     *
     * @return ComActivitiesActivityLoggerInterface
     */
    public function setActions($actions)
    {
        $this->_actions = $actions;
        return $this;
    }

    /**
     * Get the activity object.
     *
     * The activity object is the entity on which the action is executed.
     *
     * @param KCommandInterface $command The command.
     *
     * @return KModelEntityInterface The activity object.
     */
    public function getActivityObject(KCommandInterface $command)
    {
        $parts = explode('.', $command->getName());

        // Properly fetch data for the event.
        if ($parts[0] == 'before') {
            $object = $command->getSubject()->getModel()->fetch();
        } else {
            $object = $command->result;
        }

        return $object;
    }

    /**
     * Get the activity status.
     *
     * The activity status is the current status of the activity object.
     *
     * @param KModelEntityInterface $object The activity object.
     * @param string                $action The action being executed.
     * @return string The activity status.
     */
    public function getActivityStatus(KModelEntityInterface $object, $action = null)
    {
        $status = $object->getStatus();

        // Commands may change the original status of an action.
        if ($action == 'after.add' && $status == KModelEntityInterface::STATUS_UPDATED) {
            $status = KModelEntityInterface::STATUS_CREATED;
        }

        // Ignore non-changing edits.
        if ($action == 'after.edit' && $status == KModelEntityInterface::STATUS_FETCHED) {
            $status = null;
        }

        return $status;
    }

    /**
     * Get the activity subject.
     *
     * The activity subject is the identifier of the object that executes the action.
     *
     * @param KCommandInterface $command The command.
     * @return KObjectIdentifier The activity subject.
     */
    public function getActivitySubject(KCommandInterface $command)
    {
        return $command->getSubject()->getIdentifier();
    }

    /**
     * Get the activity data.
     *
     * @param KModelEntityInterface      $object  The activity object.
     * @param KObjectIdentifierInterface $subject The activity subject.
     * @param string                     $action  The action being executed
     * @return array Activity data.
     */
    public function getActivityData(KModelEntityInterface $object, KObjectIdentifierInterface $subject, $action = null)
    {
        $data = array(
            'application' => $subject->domain,
            'type'        => $subject->type,
            'package'     => $subject->package,
            'name'        => $subject->name,
        );

        if (is_array($this->_title_column))
        {
            foreach ($this->_title_column as $title)
            {
                if ($object->{$title})
                {
                    $data['title'] = $object->{$title};
                    break;
                }
            }
        }
        elseif ($object->{$this->_title_column}) {
            $data['title'] = $object->{$this->_title_column};
        }

        if (!isset($data['title'])) {
            $data['title'] = '#' . $object->id;
        }

        $data['row'] = $object->id;

        return $data;
    }
}
