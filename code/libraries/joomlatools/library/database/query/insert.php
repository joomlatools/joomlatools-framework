<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Insert Database Query
 *
 * @author  Gergo Erdosi <https://github.com/gergoerdosi>
 * @package Koowa\Library\Database\Query
 */
class KDatabaseQueryInsert extends KDatabaseQueryAbstract
{
    /**
     * The table name.
     *
     * @var string
     */
    public $table;

    /**
     * Update type
     *
     * Possible values are INSERT|REPLACE|INSERT IGNORE
     *
     * @var string
     */
    public $type = 'INSERT';

    /**
     * Array of column names.
     *
     * @var array
     */
    public $columns = array();

    /**
     * Array of values.
     *
     * @var array
     */
    public $values = array();

    /**
     * Array of values for the update statement coming after ON DUPLICATE KEY UPDATE
     *
     * @var array
     */
    public $duplicate_key_values = array();

    /**
     * Sets insert operation type
     *
     * Possible values are INSERT|REPLACE|INSERT IGNORE
     *
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $type = strtoupper($type);

        if (!in_array($type, ['INSERT', 'INSERT IGNORE', 'REPLACE'])) {
            throw new UnexpectedValueException('Invalid insert type');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Runs the operation as a REPLACE
     *
     * @return $this
     */
    public function replace()
    {
        $this->type('REPLACE');

        return $this;
    }

    /**
     * Runs the operation as INSERT IGNORE
     *
     * @return $this
     */
    public function ignore()
    {
        $this->type('INSERT IGNORE');

        return $this;
    }

    /**
     * Adds an ON DUPLICATE KEY VALUES clause to the end of the query
     *
     * @link https://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html
     * @param $values
     * @return $this
     */
    public function onDuplicateKey($values)
    {
        $this->duplicate_key_values = array_merge($this->duplicate_key_values, (array) $values);

        return $this;
    }

    /**
     * Build the table clause 
     *
     * @param  string $table The table name.
     * @return KDatabaseQueryInsert
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Build the columns clause 
     *
     * @param  array $columns Array of column names.
     * @return KDatabaseQueryInsert
     */
    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Build the values clause 
     *
     * @param  array $values Array of values.
     * @return KDatabaseQueryInsert
     */
    public function values($values)
    {
        if(!$values instanceof KDatabaseQuerySelect)
        {
            if (!$this->columns && !is_numeric(key($values))) {
                $this->columns(array_keys($values));
            }

            $this->values[] = array_values($values);
        }
        else $this->values = $values;

        return $this;
    }

    /**
     * Render the query to a string.
     *
     * @return  string  The query string.
     */
    public function toString()
    {
        $adapter = $this->getAdapter();
        $prefix  = $adapter->getTablePrefix();
        $query   = $this->type;

        if($this->table) {
            $query .= ' INTO '.$adapter->quoteIdentifier($prefix.$this->table);
        }

        if($this->columns) {
            $query .= '('.implode(', ', array_map(array($adapter, 'quoteIdentifier'), $this->columns)).')';
        }

        if($this->values)
        {
            if(!$this->values instanceof KDatabaseQuerySelect)
            {
                $query .= ' VALUES'.PHP_EOL;

                $values = array();
                foreach ($this->values as $row)
                {
                    $data = array();
                    foreach($row as $column) {
                        $data[] = $adapter->quoteValue(is_object($column) ? (string) $column : $column);
                    }

                    $values[] = '('.implode(', ', $data).')';
                }

                $query .= implode(', '.PHP_EOL, $values);
            }
            else $query .= ' '.$this->values;
        }

        if($this->duplicate_key_values && $this->type === 'INSERT')
        {
            $values = array();
            foreach($this->duplicate_key_values as $value) {
                $values[] = ' '. $adapter->quoteIdentifier($value);
            }

            $update_clause = implode(', ', $values);

            if($this->_parameters) {
                $update_clause = $this->_replaceParams($update_clause);
            }

            $query .= ' ON DUPLICATE KEY UPDATE '.$update_clause;
        }

        return $query;
    }
}
