<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Job interface
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
abstract class ComSchedulerJobAbstract extends KObject implements ComSchedulerJobInterface
{
    /**
     * Prioritized flag
     *
     * @var bool
     */
    protected $_prioritized;

    /**
     * Job frequency
     *
     * @var int
     */
    protected $_frequency;

    /**
     * @param KObjectConfig $config
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->setPrioritized($config->prioritized);
        $this->setFrequency($config->frequency);
    }

    /**
     * @param KObjectConfig $config
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'prioritized' => false,
            'frequency'   => ComSchedulerJobInterface::FREQUENCY_HOURLY
        ));
    }

    /**
     * Runs the job
     *
     * @param  ComSchedulerJobContextInterface $context Context
     * @return int The result of $this->complete() or $this->suspend()
     */
    abstract public function run(ComSchedulerJobContextInterface $context);

    /**
     * Signals the job completion
     *
     * @return int
     */
    public function complete()
    {
        return ComSchedulerJobInterface::JOB_COMPLETE;
    }

    /**
     * Signals the job suspension
     *
     * @return int
     */
    public function suspend()
    {
        return ComSchedulerJobInterface::JOB_SUSPEND;
    }

    /**
     * Signals an error in the job
     *
     * @return int
     */
    public function fail()
    {
        return ComSchedulerJobInterface::JOB_FAIL;
    }

    /**
     * Signals that there is no need to run the job
     *
     * @return int
     */
    public function skip()
    {
        return ComSchedulerJobInterface::JOB_SKIP;
    }

    /**
     * Returns the prioritized flag of the job
     *
     * @return bool
     */
    public function isPrioritized()
    {
        return $this->_prioritized;
    }

    /**
     * Sets if the job is prioritized
     * @param $prioritized bool
     * @return $this
     */
    public function setPrioritized($prioritized)
    {
        $this->_prioritized = (bool) $prioritized;

        return $this;
    }

    /**
     * Returns the job frequency in cron expression
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->_frequency;
    }

    /**
     * Sets the frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->_frequency = $frequency;

        return $this;
    }
}