<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Logger Interface.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
interface ComActivitiesActivityLoggerInterface
{
    /**
     * Log an activity.
     *
     * @param string                     $action  The action to log.
     * @param KModelEntityInterface      $object  The activity object on which the action is performed.
     * @param KObjectIdentifierInterface $subject The activity subject who is performing the action.
     */
    public function log($action, KModelEntityInterface $object, KObjectIdentifierInterface $subject);

    /**
     * Return the list of actions the logger should listen to for logging.
     *
     * @return array The list of actions.
     */
    public function getActions();

    /**
     * Set the list of actions the logger should listen to for logging.
     *
     * @param array $actions The list of actions.
     *
     * @return ComActivitiesActivityLoggerInterface
     */
    public function setActions($actions);

    /**
     * Get the activity object.
     *
     * The activity object is the entity on which the action is executed.
     *
     * @param KCommandInterface $command The command.
     * @return KModelEntityInterface The activity object.
     */
    public function getActivityObject(KCommandInterface $command);

    /**
     * Get the activity subject.
     *
     * The activity subject is the identifier of the object that executes the action.
     *
     * @param KCommandInterface $command The command.
     * @return KObjectIdentifier The activity subject.
     */
    public function getActivitySubject(KCommandInterface $command);

    /**
     * Get the activity status.
     *
     * The activity status is the current status of the activity object.
     *
     * @param KModelEntityInterface $object The activity object.
     * @param string                $action The action being executed.
     * @return string The activity status.
     */
    public function getActivityStatus(KModelEntityInterface $object, $action = null);

    /**
     * Get the activity data.
     *
     * @param KModelEntityInterface      $object  The activity object.
     * @param KObjectIdentifierInterface $subject The activity subject.
     * @param string                     $action  The action being executed
     * @return array Activity data.
     */
    public function getActivityData(KModelEntityInterface $object, KObjectIdentifierInterface $subject, $action = null);
}