<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Migrator Interface.
 */
interface ComMigratorMigratorInterface extends KControllerInterface
{
    /**
     * Jobs setter.
     *
     * @param array $jobs A list of jobs.
     *
     * @return $this
     */
    public function addJobs($jobs);

    /**
     * Adds a job to the queue.
     *
     * @param      string $name   The job name.
     * @param      mixed  $params The job parameters.
     *
     * @return $this
     */
    public function addJob($name, $params);

    /**
     * Removes a job from the list
     *
     * @param string $name Job name
     * @return $this
     */
    public function removeJob($name);

    /**
     * Job getter.
     *
     * @param string $name The job name.
     *
     * @return mixed The job.
     */
    public function getJob($name);

    /**
     * Checks if a job is supported by the exporter.
     *
     * @param string $name The job name.
     *
     * @return bool True if it is, false otherwise.
     */
    public function hasJob($name);
}