<?php
/**
 * Tagging Component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */

/**
 * Tags Model
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsModelTags extends KModelDatabase
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        // Set the state
        $this->getState()
            ->insert('row', 'cmd')
            ->insert('created_by', 'int');
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KObjectConfig $config 	An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array('searchable'),
        ));

        parent::_initialize($config);
    }

    /**
     * Method to get a table object
     *
     * @return KDatabaseTableInterface
     */
    final public function getTable()
    {
        if(!($this->_table instanceof KDatabaseTableInterface)) {
            $this->_table = $this->getObject('com:tags.database.table.tags', array('name' => $this->_table));
        }

        return $this->_table;
    }

    /**
     * Method to set a table object attached to the model
     *
     * @param	string	$table The table name
     * @return  ComTagsModelTags
     */
    final public function setTable($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * Builds SELECT columns list for the query
     *
     * @param KDatabaseQuerySelect $query
     */
    protected function _buildQueryColumns(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryColumns($query);

        $query->columns(array(
            'count' => 'COUNT( relations.tag_id )'
        ));

        if($this->getState()->row)
        {
            $query->columns(array(
                'row' => 'relations.row'
            ));
        }
    }

    /**
     * Builds GROUP BY clause for the query
     *
     * @param KDatabaseQuerySelect $query
     */
    protected function _buildQueryGroup(KDatabaseQueryInterface $query)
    {
        $query->group('tbl.slug');
    }

    /**
     * Builds JOINS clauses for the query
     *
     * @param KDatabaseQuerySelect $query
     */
    protected function _buildQueryJoins(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryJoins($query);

        if (!$query->isCountQuery())
        {
            $table = $this->getTable()->getName();

            $query->join(array('relations' => $table.'_relations'), 'relations.tag_id = tbl.tag_id');
        }
    }

    /**
     * Builds WHERE clause for the query
     *
     * @param KDatabaseQuerySelect $query
     */
    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        $state = $this->getState();

        if(!$query->isCountQuery() && $state->row) {
            $query->where('relations.row IN :row')->bind(array('row' => (array) $this->getState()->row));
        }

        if ($state->created_by)
        {
            $query->where('tbl.created_by IN :created_by')
                ->bind(array('created_by' => (array) $state->created_by));
        }

        parent::_buildQueryWhere($query);
    }
}
