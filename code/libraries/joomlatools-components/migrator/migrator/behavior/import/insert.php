<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */


class ComMigratorMigratorBehaviorImportInsert extends KControllerBehaviorAbstract
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
            'insert_limit' => '100'
        ));

        parent::_initialize($config);
    }

    /**
     * Insert task handler.
     *
     * Insert data from files to database tables.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array The task output.
     *
     * @throws RuntimeException
     */
    protected function _actionInsert(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'format' => 'csv',
            'data' => array(
                'offset' => (int) $this->getRequest()->getData()->offset
            )
        ));

        $table  = $job->table;
        $offset = $job->data->offset;
        $source = $job->source;
        $format = $job->format;

        if ($offset == 0 && $job->create_from)
        {
            $db = $this->getObject('lib:database.adapter.mysqli');

            $query = $db->replaceTableNeedle('DROP TABLE IF EXISTS #__'.$job->table);
            $db->execute($query);

            $query = $db->replaceTableNeedle(sprintf('CREATE TABLE #__%s LIKE #__%s', $job->table, $job->create_from));
            $db->execute($query);
        }

        $limit = $job->insert_limit;

        $file = sprintf('%s/%s.%s', $job->folder, $source, $format);

        if (!file_exists($file)) {
            throw new RuntimeException('The file to be inserted does not exists');
        }

        $file = new SplFileObject($file);

        $method = sprintf('_insert%s', ucfirst($format));

        if (method_exists($this, $method))
        {
            $inserted = $this->$method($file, $table, $offset, $limit);

            // Rewind to count the total number of lines
            $file->rewind();

            $total     = iterator_count($file)-1;
            $last_line = $offset + $inserted;
            $remaining = $total-$last_line;

            $output = array(
                'completed' => $inserted,
                'total'     => $total,
                'remaining' => $remaining,
                'offset'    => $last_line
            );
        }
        else throw new RuntimeException('Unknown format type: ' . $format);

        return $output;
    }

    /**
     * Inserts CSV data into a database table.
     *
     * @param SplFileObject $file   The file containing the data.
     * @param string        $table  The table name.
     * @param int           $offset The insert offset.
     * @param int           $limit  The insert limit.
     *
     * @return int The amount of data rows that got inserted.
     */
    protected function _insertCSV(SplFileObject $file, $table, $offset = 0, $limit = 100)
    {
        $db    = $this->getObject('lib:database.adapter.mysqli');
        $query = $this->getObject('lib:database.query.insert');

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '"');

        $query->table($table);

        $unset = array();

        $rows_per_query = 20;
        $queue_count    = 0;
        $total_count    = 0;

        foreach ($file as $i => $row)
        {
            if ($i === 0)
            {
                $row = str_replace("\xEF\xBB\xBF", '', $row);

                $unset = $this->_getIgnoredOffsets($row, $query->table);

                $this->_unsetOffsets($row, $unset);

                $query->columns($row);

                continue;
            }

            if ($offset && $i <= $offset) {
                continue;
            }

            $this->_unsetOffsets($row, $unset);

            $query->values($row);
            $total_count++;
            $queue_count++;

            if ($queue_count === $rows_per_query)
            {
                $db->execute(str_replace('INSERT', 'INSERT IGNORE', $query->toString()));
                $query->values = array();
                $queue_count   = 0;
            }

            if ($limit && $total_count === $limit) {
                break;
            }
        }

        // There are some rows pending insert
        if ($queue_count) {
            $db->execute(str_replace('INSERT', 'INSERT IGNORE', $query->toString()));
        }

        return $total_count;
    }


    /**
     * Returns a list of array offsets to be ignored
     *
     * The ignored offsets correspond to the offsets from a list of columns that are not present in a given table.
     *
     * @param array  $columns An array containing column names.
     * @param string $table   The table name.
     *
     * @return array An array containing the ignored offsets.
     */
    protected function _getIgnoredOffsets(array $columns, $table)
    {
        $dbo = JFactory::getDbo();

        $table_columns = array_keys($dbo->getTableColumns($dbo->getPrefix() . $table));

        $result = array();

        foreach ($columns as $i => $column)
        {
            if (!in_array($column, $table_columns)) {
                $result[] = $i;
            }
        }

        return $result;
    }

    /**
     * Unsets a list of offsets from an array.
     *
     * @param array $array   The array to unset offsets from.
     * @param array $offsets An array of offsets to unset.
     */
    protected function _unsetOffsets(&$array, array $offsets)
    {
        foreach ($offsets as $offset)
        {
            unset($array[$offset]);
        }
    }
}