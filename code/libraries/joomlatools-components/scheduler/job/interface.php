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
interface ComSchedulerJobInterface extends KObjectInterface
{
    const JOB_COMPLETE = 0;
    const JOB_SUSPEND  = -1;
    const JOB_FAIL     = 1;
    const JOB_SKIP     = 2;

    const FREQUENCY_EVERY_MINUTE       = '* * * * *';
    const FREQUENCY_EVERY_FIVE_MINUTES = '*/5 * * * *';
    const FREQUENCY_EVERY_TEN_MINUTES  = '*/10 * * * *';
    const FREQUENCY_EVERY_QUARTER_HOUR = '*/15 * * * *';
    const FREQUENCY_EVERY_HALF_HOUR    = '*/30 * * * *';
    const FREQUENCY_HOURLY             = '0 * * * *';
    const FREQUENCY_DAILY              = '0 0 * * *';
    const FREQUENCY_WEEKLY             = '0 0 * * 0';
    const FREQUENCY_MONTHLY            = '0 0 1 * *';
    const FREQUENCY_YEARLY             = '0 0 1 1 *';

    /**
     * Runs the job
     *
     * @param  ComSchedulerJobContextInterface $context Context
     * @return int The result of $this->complete() or $this->suspend()
     */
    public function run(ComSchedulerJobContextInterface $context);

    /**
     * Signals the job completion
     *
     * @return int
     */
    public function complete();

    /**
     * Signals the job suspension
     *
     * @return int
     */
    public function suspend();

    /**
     * Signals an error in the job
     *
     * @return int
     */
    public function fail();

    /**
     * Returns the prioritized flag of the job
     *
     * @return bool
     */
    public function isPrioritized();

    /**
     * Set tif the job is prioritized
     * @param $prioritized bool
     * @return $this
     */
    public function setPrioritized($prioritized);

    /**
     * Returns the job frequency in cron expression
     *
     * @return string
     */
    public function getFrequency();

    /**
     * Sets the frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency);
}