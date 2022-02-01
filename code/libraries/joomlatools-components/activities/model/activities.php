<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activities Model.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesModelActivities extends KModelDatabase
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $state = $this->getState();

        $state->insert('application', 'cmd')
              ->insert('type', 'cmd')
              ->insert('package', 'cmd')
              ->insert('name', 'cmd')
              ->insert('action', 'cmd')
              ->insert('row', 'string')
              ->insert('user', 'cmd')
              ->insert('start_date', 'date')
              ->insert('end_date', 'date')
              ->insert('day_range', 'int')
              ->insert('ip', 'ip');

        $state->remove('direction')->insert('direction', 'word', 'desc');

        // Force ordering by created_on
        $state->sort = 'created_on';
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('behaviors' => array('searchable')));
        parent::_initialize($config);
    }

    /**
     * Builds WHERE clause for the query.
     *
     * @param KDatabaseQueryInterface $query
     */
    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($state->application) {
            $query->where('tbl.application = :application')->bind(array('application' => $state->application));
        }

        if ($state->type) {
            $query->where('tbl.type = :type')->bind(array('type' => $state->type));
        }

        if ($state->package) {
            $query->where('tbl.package IN :package')->bind(array('package' => (array) $state->package));
        }

        if ($state->name) {
            $query->where('tbl.name = :name')->bind(array('name' => $state->name));
        }

        if ($state->action) {
            $query->where('tbl.action IN (:action)')->bind(array('action' => $state->action));
        }

        if ($state->row) {
            $query->where('tbl.row IN (:row)')->bind(array('row' => $state->row));
        }

        if ($state->start_date && $state->start_date != '0000-00-00')
        {
            $start_date = $this->getObject('lib:date',array('date' => $state->start_date));

            $query->where('DATE(tbl.created_on) >= :start')->bind(array('start' => $start_date->format('Y-m-d')));

            if (is_numeric($state->day_range)) {
                $query->where('DATE(tbl.created_on) <= :range_start')->bind(array('range_start' => $start_date->modify(sprintf('+%d days', $state->day_range))->format('Y-m-d')));
            }
        }

        if ($state->end_date && $state->end_date != '0000-00-00')
        {
            $end_date  = $this->getObject('lib:date',array('date' => $state->end_date));

            $query->where('DATE(tbl.created_on) <= :end')->bind(array('end' => $end_date->format('Y-m-d')));

            if (is_numeric($state->day_range)) {
                $query->where('DATE(tbl.created_on) >= :range_end')->bind(array('range_end' => $end_date->modify(sprintf('-%d days', $state->day_range))->format('Y-m-d')));
            }
        }

        if (is_numeric($state->user)) {
            $query->where('tbl.created_by = :created_by')->bind(array('created_by' => $state->user));
        }

        if ($ip = $state->ip) {
            $query->where('tbl.ip IN (:ip)')->bind(array('ip' => $state->ip));
        }
    }
}
