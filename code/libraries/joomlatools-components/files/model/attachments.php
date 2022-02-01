<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachments Model
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelAttachments extends KModelDatabase
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('behaviors' => array('attachable')));
        parent::_initialize($config);
    }

    protected function _buildQueryColumns(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns(array('tbl.*', 'container_slug' => 'containers.slug'));
    }

    protected function _buildQueryJoins(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        $query->join('files_containers AS containers', 'containers.files_container_id = tbl.files_container_id', 'INNER');
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();

        if (!$state->isUnique())
        {
            if ($container = $state->container) {
                $query->where('tbl.files_container_id = :container');
            }

            if ($name = $state->name)
            {
                $query->where('tbl.name = :name');
            }

            $query->bind(array('container' => $container, 'name' => $name));
        }
    }

    /**
     * Overridden for pushing the container value.
     */
    protected function _actionCreate(KModelContext $context)
    {
        $context->entity->append(array(
            'container' => $context->state->container,
        ));

        return parent::_actionCreate($context);
    }
}