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

    /**
     * Override fetch method to handle caching of unique result
     * 
     * @param KModelContext $context
     * @return KModelEntityInterface
     */
    protected function _actionFetch(KModelContext $context)
    {
        $state = $this->getState();
        
        if ($state->isUnique() && $state->has('slug'))
        {
            $slug = $state->slug;

            if (!isset(self::$containers[$slug]))
            {
                $container = parent::_actionFetch($context);

                if (!$container->isNew()) {
                    self::$containers[$slug] = $container;
                }
            }
            else $container = self::$containers[$slug];
        }
        else $container = parent::_actionFetch($context);

        return $container;
    }
}
