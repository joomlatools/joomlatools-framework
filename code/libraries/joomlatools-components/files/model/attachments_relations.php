<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Relations Attachments Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelAttachments_relations extends KModelDatabase
{
    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!$state->isUnique())
        {
            if ($table = $state->table) {
                $query->where('table = :table');
            }

            if ($row = $state->row) {
                $query->where('row = :row');
            }

            $column = $this->getConfig()->relation_column;

            if ($id = $state->{$column}) {
                $query->where("{$column} = :id");
            }

            $query->bind(array('table' => $table, 'row' => $row, 'id' => $id));
        }
    }
}