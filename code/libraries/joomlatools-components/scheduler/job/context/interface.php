<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Job context interface
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
interface ComSchedulerJobContextInterface extends KControllerContextInterface
{
    /**
     * Sets the time it took to complete the job
     *
     * @param $time float Duration in ms
     */
    public function setJobDuration($time);

    /**
     * Returns the time it took to run the last job
     *
     * @return float
     */
    public function getJobDuration();

    /**
     * Sets the time limit
     *
     * @param $time int Unix timestamp
     */
    public function setTimeLimit($time);

    /**
     * Returns if the job has time left to run.
     * If the method returns false the job should save state and call suspend as soon as possible.
     *
     * Condition is passed by the dispatcher, usually only when the job is run in an HTTP context
     *
     * @return boolean
     */
    public function hasTimeLeft();

    /**
     * Returns the remaining time for the job to run
     *
     * @return int
     */
    public function getTimeLeft();

    /**
     * Returns the job state
     *
     * @return KObjectConfigInterface
     */
    public function getState();

    /**
     * Sets the time limit
     *
     * @param KObjectConfig|array $state
     * @return $this
     */
    public function setState($state);

    /**
     * Logs a message for debugging purposes
     *
     * @param $message string
     */
    public function log($message);

    /**
     * Returns the logs
     *
     * @return array
     */
    public function getLogs();
    /**
     * Sets the logs
     *
     * @param array $logs
     */
    public function setLogs(array $logs);
}