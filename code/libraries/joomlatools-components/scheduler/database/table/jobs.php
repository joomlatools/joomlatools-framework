<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Jobs table
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
class ComSchedulerDatabaseTableJobs extends KDatabaseTableAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'orderable',
                'lib:database.behavior.modifiable',
                'identifiable',
                'parameterizable' => array('column' => 'state')
            ),
            'filters' => array(
                'state' => 'json'
            )
        ));

        parent::_initialize($config);
    }
}
