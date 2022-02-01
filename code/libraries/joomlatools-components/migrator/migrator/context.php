<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Job context
 *
 */
class ComMigratorMigratorContext extends KControllerContext
{
    /**
     * @var KObjectConfig
     */
    protected $_job;

    /**
     * @var string
     */
    protected $_error;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error)
    {
        $this->_error = $error;

        return $this;
    }

    /**
     * @return KObjectConfig
     */
    public function getJob()
    {
        return $this->_job;
    }

    /**
     * @param KObjectConfig $job
     * @return $this
     */
    public function setJob(KObjectConfig $job)
    {
        $this->_job = $job;

        return $this;
    }
}