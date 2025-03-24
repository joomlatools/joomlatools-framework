<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Containers Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelContainers extends KModelDatabase
{
    public static $containers = array();

	protected function _buildQueryWhere(KDatabaseQueryInterface $query)
	{
		parent::_buildQueryWhere($query);

        $state = $this->getState();

		if ($state->search) {
            $query->where('tbl.title LIKE :search')->bind(array('search' =>  '%'.$state->search.'%'));
        }
	}

    protected function _actionFetch(KModelContext $context)
    {
        $state = $this->getState();
        $slug = $state->slug;

        if ($state->isUnique() && $state->has('slug') && !isset(self::$containers[$slug])) {
            self::$containers[$slug] = parent::_actionFetch(($context));
        }

        return self::$containers[$slug];
    }
}
