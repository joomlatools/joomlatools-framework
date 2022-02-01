<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Orderable behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
class ComSchedulerDatabaseBehaviorOrderable extends KDatabaseBehaviorOrderable
{
    public function _buildQueryWhere($query)
    {
        $query->where('queue = :queue')->bind(array('queue' => $this->queue));
    }

    protected function _beforeInsert(KDatabaseContextInterface $context)
    {
        if($this->hasProperty('ordering'))
        {
            if ($this->ordering == -PHP_INT_MAX) {
                $this->ordering = $this->getMinOrdering() - 1;
            }
            elseif($this->ordering <= 0 || $this->ordering == PHP_INT_MAX) {
                $this->ordering = $this->getMaxOrdering() + 1;
            }
        }
    }

    protected function _beforeUpdate(KDatabaseContextInterface $context)
    {
        return $this->_beforeInsert($context);
    }

    protected function _afterInsert(KDatabaseContextInterface $context)
    {
        $this->reorder();
    }

    protected function _afterUpdate(KDatabaseContextInterface $context)
    {
        $this->reorder();
    }

    /**
     * Find the maximum ordering within this parent
     *
     * @return int
     */
    protected function getMinOrdering()
    {
        $table  = $this->getTable();
        $db     = $table->getAdapter();

        $query = $this->getObject('lib:database.query.select')
            ->columns('MIN(ordering)')
            ->table($table->getName());

        $this->_buildQueryWhere($query);

        return (int) $db->select($query, KDatabase::FETCH_FIELD);
    }
}