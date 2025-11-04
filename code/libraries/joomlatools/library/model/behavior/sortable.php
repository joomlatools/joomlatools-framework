<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Sortable Model Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Model\Behavior
 */
class KModelBehaviorSortable extends KModelBehaviorAbstract
{
    /**
     * Insert the model states
     *
     * @param KObjectMixable $mixer
     */
    public function onMixin(KObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        $mixer->getState()
            ->insert('sort', 'cmd')
            ->insert('direction', 'word', 'asc');
    }

    /**
     * Add order query
     *
     * @param   KModelContextInterface $context A model context object
     *
     * @return    void
     */
    protected function _beforeFetch(KModelContextInterface $context)
    {
        $model = $context->getSubject();

        if ($model instanceof KModelDatabase && !$context->state->isUnique())
        {
            $state = $context->state;

            $column    = $state->sort;
            $direction = strtoupper($state->direction ?? '');

            $this->_sort($column, $direction, $context->query);
        }
    }

    protected function _sort($column, $direction, KDatabaseQuerySelect $query)
    {
        $columns = array_keys($this->getTable()->getColumns());

        $parts = explode('.', $column ?? '');

        if (isset($parts[1])) {
            $alias = $parts[0];
        } else {
            $alias = false;
        }

        if ($column)
        {
            if (!$alias) {
                $column = $this->getTable()->mapColumns($column);
            }

            //if(in_array($column, $columns)) {
            $query->order($column, $direction);
            //}
        }

        if (!$alias && $column != 'ordering' && in_array('ordering', $columns)) {
            $query->order('tbl.ordering', 'ASC');
        }
    }
}