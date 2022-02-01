<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Resources Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesModelResources extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
             ->insert('uuid', 'string')
             ->insert('package', 'cmd')
             ->insert('name', 'cmd')
             ->insert('resource_id', 'string')
             ->insert('title', 'string')
             ->insert('package_name', 'cmd');
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if ($uuid = $state->uuid) {
            $query->where('tbl.uuid = :uuid')->bind(array('uuid' => $uuid));
        }

        if ($package = $state->package) {
            $query->where('tbl.package = :package')->bind(array('package' => $package));
        }

        if ($name = $state->name) {
            $query->where('tbl.name = :name')->bind(array('name' => $name));
        }

        if ($resource_id = $state->resource_id) {
            $query->where('tbl.resource_id = :resource_id')->bind(array('resource_id' => $resource_id));
        }

        if ($title = $state->title) {
            $query->where('tbl.title LIKE :title')->bind(array('title' => '%' . $title . '%'));
        }

        if ($package_name = (array) $state->package_name)
        {
            $conditions = array();

            $i = 0;

            foreach ($package_name as $value)
            {
                $conditions[] = "(tbl.package = :package{$i} AND tbl.name = :name{$i})";

                list($package, $name) = explode('.', $value);

                $query->bind(array("package{$i}" => $package, "name{$i}" => $name));

                $i++;
            }

            $query->where('(' . implode(' OR ', $conditions) . ')');
        }
    }
}