<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Database
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database
 */
class KDatabase extends KObjectDecorator implements KObjectSingleton, KDatabaseAdapterInterface
{
    /**
     * Database result mode
     */
    const RESULT_STORE = 0;
    const RESULT_USE   = 1;

    /**
     * Database fetch mode
     */
    const FETCH_ARRAY       = 0;
    const FETCH_ARRAY_LIST  = 1;
    const FETCH_FIELD       = 2;
    const FETCH_FIELD_LIST  = 3;
    const FETCH_OBJECT      = 4;
    const FETCH_OBJECT_LIST = 5;

    const FETCH_ROW         = 6;
    const FETCH_ROWSET      = 7;

    /**
     * Row states
     */
    const STATUS_FETCHED  = 'fetched';
    const STATUS_DELETED  = 'deleted';
    const STATUS_CREATED  = 'created';
    const STATUS_UPDATED  = 'updated';
    const STATUS_FAILED   = 'failed';

    protected function _initialize(KObjectConfig $config)
    {
        $config->append([
            'delegate' => KObjectManager::getInstance()->getObject('database.adapter.mysqli'),
        ]);

        parent::_initialize($config);
    }

    /**
     * Connect to the db
     *
     * @return  KDatabaseAdapterAbstract
     */
    public function connect()
    {
        return $this->getDelegate()->connect();
    }

    /**
     * Reconnect to the db
     *
     * @return  KDatabaseAdapterAbstract
     */
    public function reconnect()
    {
        return $this->getDelegate()->reconnect();
    }

    /**
     * Disconnect from db
     *
     * @return  KDatabaseAdapterAbstract
     */
    public function disconnect()
    {
        return $this->getDelegate()->disconnect();
    }

    /**
     * Get the connection
     *
     * Provides access to the underlying database connection. Useful for when you need to call a proprietary method
     * on the database driver
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->getDelegate()->getConnection();
    }

    /**
     * Set the connection
     *
     * @param 	resource 	$resource The connection resource
     * @return  KDatabaseAdapterAbstract
     */
    public function setConnection($resource)
    {
        return $this->getDelegate()->setConnection($resource);
    }

    /**
     * Determines if the connection to the server is active.
     *
     * @return      boolean
     */
    public function isConnected()
    {
        return $this->getDelegate()->isConnected();
    }

    /**
     * Get the insert id of the last insert operation
     *
     * @return mixed The id of the last inserted row(s)
     */
    public function getInsertId()
    {
        return $this->getDelegate()->getInsertId();
    }

    /**
     * Retrieves the column schema information about the given table
     *
     * @param 	string 	$table A table name
     * @return	KDatabaseSchemaTable
     */
    public function getTableSchema($table)
    {
        return $this->getDelegate()->getTableSchema($table);
    }

    /**
     * Lock a table.
     *
     * @param  string  $table The name of the table
     * @return boolean True on success, false otherwise.
     */
    public function lockTable($table)
    {
        return $this->getDelegate()->lockTable($table);
    }

    /**
     * Unlock a table.
     *
     * @return boolean True on success, false otherwise.
     */
    public function unlockTable()
    {
        return $this->getDelegate()->unlockTable();
    }

    /**
     * Perform a select query.
     *
     * @param   KDatabaseQueryInterface  $query A full SQL query to run. Data inside the query should be properly escaped.
     * @param   integer $mode   The result mode, either the constant KDatabase::RESULT_USE or KDatabase::RESULT_STORE
     *                          depending on the desired behavior. By default, KDatabase::RESULT_STORE is used. If you
     *                          use KDatabase::RESULT_USE all subsequent calls will return error Commands out of sync
     *                          unless you free the result first.
     * @param   string $key  The column name of the index to use.
     * @return  mixed If successful returns a result object otherwise FALSE
     */
    public function select(KDatabaseQueryInterface $query, $mode = KDatabase::RESULT_STORE, $key = '')
    {
        return $this->getDelegate()->select($query, $mode, $key);
    }

    /**
     * Insert a row of data into a table.
     *
     * @param KDatabaseQueryInsert $query The query object.
     * @return bool|integer  If the insert query was executed returns the number of rows updated, or 0 if
     *                       no rows where updated, or -1 if an error occurred. Otherwise FALSE.
     */
    public function insert(KDatabaseQueryInsert $query)
    {
        return $this->getDelegate()->insert($query);
    }

    /**
     * Update a table with specified data.
     *
     * @param  KDatabaseQueryUpdate $query The query object.
     * @return integer  If the update query was executed returns the number of rows updated, or 0 if
     *                  no rows where updated, or -1 if an error occurred. Otherwise FALSE.
     */
    public function update(KDatabaseQueryUpdate $query)
    {
        return $this->getDelegate()->update($query);
    }

    /**
     * Delete rows from the table.
     *
     * @param  KDatabaseQueryDelete $query The query object.
     * @return integer 	Number of rows affected, or -1 if an error occurred.
     */
    public function delete(KDatabaseQueryDelete $query)
    {
        return $this->getDelegate()->delete($query);
    }

    /**
     * Use and other queries that don't return rows
     *
     * @param  string   $sql  The query to run. Data inside the query should be properly escaped.
     * @param  integer  $mode The result made, either the constant KDatabase::RESULT_USE or KDatabase::RESULT_STORE
     *                  depending on the desired behavior. By default, KDatabase::RESULT_STORE is used. If you
     *                  use KDatabase::RESULT_USE all subsequent calls will return error Commands out of sync
     *                  unless you free the result first.
     * @throws \KDatabaseException If the query could not be executed
     * @return boolean  For SELECT, SHOW, DESCRIBE or EXPLAIN will return a result object.
     *                  For other successful queries  return TRUE.
     */
    public function execute($sql, $mode = KDatabase::RESULT_STORE )
    {
        return $this->getDelegate()->execute($sql, $mode);
    }

    /**
     * Set the table prefix
     *
     * @param string $prefix The table prefix
     * @return KDatabaseAdapterAbstract
     * @see KDatabaseAdapterAbstract::replaceTableNeedle
     */
    public function setTablePrefix($prefix)
    {
        return $this->getDelegate()->setTablePrefix($prefix);
    }

    /**
     * Get the table prefix
     *
     * @return string The table prefix
     * @see KDatabaseAdapterAbstract::replaceTableNeedle
     */
    public function getTablePrefix()
    {
        return $this->getDelegate()->getTablePrefix();
    }

    /**
     * Get the table needle
     *
     * @return string The table needle
     * @see KDatabaseAdapterAbstract::replaceTableNeedle
     */
    public function getTableNeedle()
    {
        return $this->getDelegate()->getTableNeedle();
    }

    /**
     * This function replaces the table needles in a query string with the actual table prefix.
     *
     * @param  string 	$sql The SQL query string
     * @return string	The SQL query string
     */
    public function replaceTableNeedle( $sql )
    {
        return $this->getDelegate()->replaceTableNeedle( $sql );
    }

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted and then returned as a comma-separated string;
     * this is useful for generating IN() lists.
     *
     * @param   mixed $value The value to quote.
     * @return string An SQL-safe quoted value (or a string of separated-
     *                and-quoted values).
     */
    public function quoteValue($value)
    {
        return $this->getDelegate()->quoteValue($value);
    }

    /**
     * Quotes a single identifier name (table, table alias, table column, index, sequence).  Ignores empty values.
     *
     * This function requires all SQL statements, operators and functions to be uppercased.
     *
     * @param string|array The identifier name to quote.  If an array, quotes each element in the array as an
     *                      identifier name.
     * @return string|array The quoted identifier name (or array of names).
     */
    public function quoteIdentifier($spec)
    {
        return $this->getDelegate()->quoteIdentifier($spec);
    }

    /**
     * Returns a query object with the current adapter set
     *
     * @param string|KObjectIdentifier $identifier Query type (e.g. `select`, `insert`) or a full identifier
     * @return KDatabaseQueryInterface
     */
    public function getQuery($identifier)
    {
        return $this->getDelegate()->getQuery($identifier);
    }
}
