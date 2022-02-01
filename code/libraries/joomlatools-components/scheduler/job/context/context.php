<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Job context
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
class ComSchedulerJobContext extends KControllerContext implements ComSchedulerJobContextInterface
{
    /**
     * @var array
     */
    protected $_logs = array();

    /**
     * Unix timestamp to finish the task
     *
     * @var int
     */
    protected $_time_limit;

    /**
     * @var float
     */
    protected $_job_duration;

    /**
     * Sets the time limit
     *
     * @param $time int Unix timestamp
     */
    public function setTimeLimit($time)
    {
        $this->_time_limit = $time;
    }

    /**
     * Returns if the job has time left to run.
     * If the method returns false the job should save state and call suspend as soon as possible.
     *
     * Condition is passed by the dispatcher, usually only when the job is run in an HTTP context
     *
     * @return boolean
     */
    public function hasTimeLeft()
    {
        return $this->_time_limit ? (time() < $this->_time_limit) : true;
    }

    /**
     * Returns the remaining time for the job to run
     *
     * @return int
     */
    public function getTimeLeft()
    {
        return $this->_time_limit ? max($this->_time_limit - time(), 0) : PHP_INT_MAX;
    }

    /**
     * Sets the time it took to complete the job
     *
     * @param $time float Duration in ms
     */
    public function setJobDuration($time)
    {
        $this->_job_duration = $time;
    }

    /**
     * Returns the time it took to run the last job
     *
     * @return float
     */
    public function getJobDuration()
    {
        return $this->_job_duration;
    }

    /**
     * Returns the job state
     *
     * @return KObjectConfigInterface
     */
    public function getState()
    {
        $result = KObjectConfig::get('state');

        if (!$result instanceof KObjectConfig)
        {
            $this->setState(array());

            $result = KObjectConfig::get('state');
        }

        return $result;
    }

    /**
     * Sets the time limit
     *
     * @param KObjectConfig|array $state
     * @return $this
     */
    public function setState($state)
    {
        return KObjectConfig::set('state', $state);
    }

    /**
     * Logs a message for debugging purposes
     *
     * @param $message string
     */
    public function log($message)
    {
        $this->_logs[] = [$message, time()];
    }

    /**
     * Returns the logs
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->_logs;
    }

    /**
     * Sets the logs
     *
     * @param array $logs
     */
    public function setLogs(array $logs)
    {
        $this->_logs = $logs;
    }
}