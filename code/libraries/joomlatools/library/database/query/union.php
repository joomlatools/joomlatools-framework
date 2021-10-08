<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Union Database Query
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database\Query
 */
class KDatabaseQueryUnion extends KDatabaseQuerySelect
{
    /**
     * Queries
     */
    public $queries = [];

    /**
     * Distinct operation
     *
     * @var boolean
     */
    public $distinct  = false;

    /**
     * UNION ALL operation
     *
     * @var boolean
     */
    public $all = false;
    
    /**
     * Add queries for the UNION operation
     *
     * @param DatabaseQuerySelect $query
     * @return $this
     */
    public function union(KDatabaseQuerySelect $query)
    {
        $this->queries[] = $query;

        return $this;
    }

    /**
     * Checks if the current query should use UNION ALL
     *
     * @return boolean
     */
    public function isUnionAllQuery()
    {
        return (bool) $this->all;
    }

    /**
     * Make the query use UNION ALL
     *
     * @return $this
     */
    public function all()
    {
        $this->all = true;

        return $this;
    }

    /**
     * Set columns in all queries
     *
     * @param array $columns
     * @return KDatabaseQuerySelect|void
     */
    public function columns($columns = array())
    {
        foreach ($this->queries as $query) {
            $query->where($columns);
        }
    }

    /**
     * Set tables in all queries
     *
     * @param $table
     * @return KDatabaseQuerySelect|void
     */
    public function table($table)
    {
        foreach ($this->queries as $query) {
            $query->table($table);
        }
    }

    /**
     * Set joins in all queries
     *
     * @param string $table
     * @param null   $condition
     * @param string $type
     * @return $this|KDatabaseQuerySelect
     */
    public function join($table, $condition = null, $type = 'LEFT')
    {
        foreach ($this->queries as $query) {
            $query->where($table, $condition, $type);
        }

        return $this;
    }

    /**
     * Set where clauses in all queries
     *
     * @param string $condition
     * @param string $combination
     * @return $this|KDatabaseQuerySelect
     */
    public function where($condition, $combination = 'AND')
    {
        foreach ($this->queries as $query) {
            $query->where($condition, $combination);
        }

        return $this;
    }

    /**
     * Set groups in all queries
     *
     * @param array|string $columns
     * @return $this|KDatabaseQuerySelect
     */
    public function group($columns)
    {
        foreach ($this->queries as $query) {
            $query->group($columns);
        }

        return $this;
    }

    /**
     * Set having constraints in all queries
     * @param   string $condition   The having condition statement
     * @param   string $combination The having combination, defaults to 'AND'
     * @return $this|KDatabaseQuerySelect
     */
    public function having($condition, $combination = 'AND')
    {
        foreach ($this->queries as $query) {
            $query->having($condition, $combination);
        }

        return $this;
    }

    /**
     * Render the query to a string
     *
     * @return  string  The completed query
     * @throws \RuntimeException When there are less than 2 queries to combine
     */
    public function toString()
    {
        if (count($this->queries) < 2) {
            throw new \RuntimeException("Union needs at least 2 SELECT queries");
        }

        $queries = [];

        foreach ($this->queries as $query) {
            $queries[] = '('.$query->toString().')';
        }

        $driver = $this->getDriver();
        $glue   = $this->all ? 'UNION ALL' : ($this->distinct ? 'UNION DISTINCT'  : 'UNION');
        $query  = implode("\n".$glue."\n", $queries);

        if($this->order)
        {
            $query .= ' ORDER BY ';

            $list = array();
            foreach($this->order as $order) {
                $list[] = $driver->quoteIdentifier($order['column']).' '.$order['direction'];
            }

            $query .= implode(' , ', $list);
        }

        if($this->limit) {
            $query .= ' LIMIT '.$this->offset.' , '.$this->limit;
        }

        if($this->_parameters) {
            $query = $this->_replaceParams($query);
        }

        return $query;
    }
}
