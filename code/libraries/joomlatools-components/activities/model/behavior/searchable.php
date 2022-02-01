<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2011 - 2017 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Searchable Model Behavior.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */

class ComActivitiesModelBehaviorSearchable extends KModelBehaviorSearchable
{
    /**
     * Overridden to dynamically add IP as searchable column when the search state value contains
     * the #ip: prefix.
     */
    protected function _buildQuery(KModelContextInterface $context)
    {
        $state = $context->getState();
        $search = $state->search;

        if ($search && !$state->isUnique())
        {
            if (strpos($search, '#ip:') === 0)
            {
                if (!in_array('ip', $this->_columns)) {
                    array_push($this->_columns, 'ip');
                }

                $state->search = str_replace('#ip:', '', $search); // cleanup for search
            }
        }

        parent::_buildQuery($context);

        if ($state->search != $search) {
            $state->search = $search; // reset search state value
        }
    }

    /**
     * Resets the columns property by making sure that ip if removed when the state gets reset.
     */
    protected function _afterReset(KModelContextInterface $context)
    {
        $reset_columns = false;

        if ($context->modified)
        {
            if (in_array('search', $context->modified->toArray())) {
                $reset_columns = true;
            }
        }
        else $reset_columns = true;

        if ($reset_columns && ($key = array_search('ip', $this->_columns))) {
            unset($this->_columns[$key]);
        }
    }
}