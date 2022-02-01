<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Jobs model
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
class ComSchedulerModelJobs extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('stale', 'boolean', false)
            ->insert('status', 'int')
            ->insert('queue', 'int');
    }

    protected function _buildQueryColumns(KDatabaseQueryInterface $query)
    {
        if (!$query->isCountQuery()) {
            $query->columns('(status = 1 AND :now > DATE_ADD(modified_on, INTERVAL 5 MINUTE)) AS stale');
        }
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        $state = $this->getState();

        $query->bind(['now' => gmdate('Y-m-d H:i:s')]);

        if ($state->stale) {
            $query->where('(status = 1 AND :now > DATE_ADD(modified_on, INTERVAL 5 MINUTE))');
        }

        if (is_numeric($state->status) || !empty($state->status))
        {
            $query->where('tbl.status IN :status')
                ->bind(array('status' => (array) $state->status));
        }

        if (is_numeric($state->queue) || !empty($state->queue))
        {
            $query->where('tbl.queue IN :queue')
                ->bind(array('queue' => (array) $state->queue));
        }
    }
}