<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Extensions model that wraps Joomla extension data
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\Model
 */
class ComKoowaModelExtensions extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('element', 'string', null, true, ['type'])
            ->insert('client_id', 'int', null)
            ->insert('folder', 'string', null)
            ->insert('type', 'string', null)
            ->insert('package_id', 'int')
            ->insert('enabled', 'int')
            ->insert('protected', 'int')
            ->insert('state', 'int');
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        $state = $this->getState();

        if (!is_null($state->state)) {
            $query->where('tbl.state IN :state')->bind(['state' => (array) $state->state]);
        }

        if (!is_null($state->protected)) {
            $query->where('tbl.protected IN :protected')->bind(['protected' => (array) $state->protected]);
        }

        if (!is_null($state->enabled)) {
            $query->where('tbl.enabled IN :enabled')->bind(['enabled' => (array) $state->enabled]);
        }

        if (!is_null($state->package_id)) {
            $query->where('tbl.package_id IN :package_id')->bind(['package_id' => (array) $state->package_id]);
        }

        if (!is_null($state->client_id)) {
            $query->where('tbl.client_id IN :client_id')->bind(['client_id' => (array) $state->client_id]);
        }

        if (!is_null($state->folder)) {
            $query->where('tbl.folder IN :folder')->bind(['folder' => (array) $state->folder]);
        }

        if (!is_null($state->type)) {
            $query->where('tbl.type IN :type')->bind(['type' => (array) $state->type]);
        }

        parent::_buildQueryWhere($query);
    }
}