<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */


class ComMigratorMigratorBehaviorExportDatabase extends KControllerBehaviorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'separator' => ',',
            'quote'     => '"',
            'limit'     => 300,
            'chunkable' => true
        ));

        parent::_initialize($config);
    }

    /**
     * Export task handler.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array|bool
     */
    protected function _actionDatabase(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'data' => array(
                'offset' => (int) $this->getRequest()->getData()->offset
            )
        ));

        // Force limit.
        $job->data->limit = $job->chunkable ? $job->limit : 0;

        $offset = $job->data->offset;
        $limit  = $job->data->limit;
        $table  = $job->table;

        $data = $this->_fetch($table, $limit, $offset, $job->callback);

        $file = new SplFileObject(sprintf('%s/%s.csv', $job->folder, $table), 'a');

        if ($offset == 0)
        {
            $file->ftruncate(0);
            $this->_writeColumns($table, $file);
        }

        while ($row = $data->fetch_assoc()) {
            $file->fwrite($this->_arrayToString($row)."\n");
        }

        if ($limit)
        {
            $total     = $this->_getTotal($table, $job->callback);
            $exported  = $data->num_rows;
            $remaining = $total - ($offset + $exported);
            $offset    = $offset + $limit;

            $output = array(
                'total'     => $total,
                'completed' => $exported,
                'remaining' => $remaining,
                'offset'    => $offset
            );
        }
        else $output = true;

        return $output;
    }

    /**
     * Writes the table columns to a CSV file
     *
     * @param string        $table The table name.
     * @param SplFileObject $file  The file to write.
     *
     * @return $this
     */
    protected function _writeColumns($table, SplFileObject $file)
    {
        $fields = array_keys($this->getObject('database.adapter.mysqli')->getTableSchema($table)->columns);

        $header = $this->_arrayToString($fields)."\n";

        $file->fwrite($header);

        return $this;
    }

    /**
     * Renders an array as CSV string.
     *
     * @param array $data The array.
     *
     * @return string The CSV string.
     */
    protected function _arrayToString($data)
    {
        $fields = array();

        $config = $this->getConfig();

        $quote = $config->quote;

        foreach($data as $value)
        {
            //Implode array's
            if(is_array($value)) {
                $value = implode(',', $value);
            }

            // Escape the quote character within the field (e.g. " becomes "")
            if ($this->_quoteValue($value))
            {
                $quoted_value = str_replace($quote, $quote.$quote, $value);
                $fields[] 	  = $quote . $quoted_value . $quote;
            }
            else $fields[] = $value;
        }

        return  implode($config->separator, $fields);
    }

    /**
     * Checks if a value should be quoted
     *
     * @param string $value The value.
     *
     * @return boolean true if it should, false otherwise.
     */
    protected function _quoteValue($value)
    {
        if(is_numeric($value)) {
            return false;
        }

        $config = $this->getConfig();

        if ($value)
        {
            if(strpos($value, $config->separator) !== false) { // Separator is present in field
                return true;
            }
    
            if(strpos($value, $config->quote) !== false) { // Quote character is present in field
                return true;
            }
    
            if (strpos($value, "\n") !== false || strpos($value, "\r") !== false ) { // Newline is present in field
                return true;
            }
    
            if(substr($value, 0, 1) == " " || substr($value, -1) == " ") {  // Space found at beginning or end of field value
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches the next result set given an offset.
     *
     * @param string $table The table name.
     * @param int    $limit The limit.
     * @param int    $offset The offset.
     * @param mixed  $callback The query condition.
     * @return MySQLi
     */
    protected function _fetch($table, $limit = 300, $offset = 0, $callback = null)
    {
        $db    = $this->getObject('database.adapter.mysqli');
        $query = $this->getObject('database.query.select')->columns('*')->table($table);

        $query->limit($limit, $offset);

        if ($pk = $this->_getPrimaryKey($table)) {
            $query->order($pk);
        }

        if (is_callable($callback)) {
            call_user_func($callback, $query);
        }

        $result = $db->execute($query);

        if (!$result) {
            throw new RuntimeException(sprintf('Database query failed %s'), $query);
        }

        return $result;
    }

    /**
     * Returns table primary key column name(s)
     *
     * @param $table string Table name (without prefix)
     * @return array
     */
    protected function _getPrimaryKey($table)
    {
        /** @var KDatabaseAdapterInterface $db */
        $db     = KObjectManager::getInstance()->getObject('database.adapter.mysqli');
        $query = /**@lang text*/"
        SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS`
        WHERE `TABLE_SCHEMA` = DATABASE() AND `COLUMN_KEY` = 'PRI' AND `TABLE_NAME` = '%s';";

        /** @var mysqli_result $result */
        $result = $db->execute(sprintf($query, $db->getTablePrefix().$table), KDatabase::RESULT_USE);
        $keys  = array();

        while ($row = $result->fetch_row()) {
            $keys[] = $row[0];
        }

        $result->free();

        return $keys;
    }

    /**
     * Counts the total number of rows in a given table.
     *
     * @param string $table The table name.
     * @param mixed  $callback The query condition.
     *
     * @return int The total amount of rows.
     */
    protected function _getTotal($table, $callback = null)
    {
        $db    = $this->getObject('database.adapter.mysqli');
        $query = $this->getObject('database.query.select')->columns('COUNT(*)')->table($table);

        if (is_callable($callback)) {
            call_user_func($callback, $query);
        }

        $result = $db->select($query, KDatabase::FETCH_FIELD);

        if (empty($result) && $result !== 0 && $result !== '0') {
            throw new RuntimeException(sprintf('There was a problem while calculating the total amount of items for table %s', $table));
        }

        return $result;
    }
}