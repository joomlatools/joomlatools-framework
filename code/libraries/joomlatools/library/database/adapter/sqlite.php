<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Sqlite Database Adapter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database\Adapter
 */
class KDatabaseAdapterSqlite extends KDatabaseAdapterPdo
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config  An optional KObjectConfig object with configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'driver'    => 'sqlite',
        ));

        parent::_initialize($config);
    }

    /**
     * Special insert method for SQlite auto-increments
     *
     * 0 is a valid value for auto-increment columns in SQLite so we have to strip them from the values
     *
     * @param KDatabaseQueryInsert $query
     * @return bool|int
     */
    public function insert(KDatabaseQueryInsert $query)
    {
        $schema = $this->getTableSchema($query->table);

        foreach ($query->columns as $order => $column) {
            if (isset($schema->columns[$column])) {
                $column_info = $schema->columns[$column];

                if ($column_info->autoinc) {
                    foreach ($query->values as &$values) {
                        if ($values[$order] === 0 || $values[$order] === '0') {
                            $values[$order] = null;
                        }
                    }
                }
            }
        }

        return parent::insert($query);
    }

    public function getTableSchema($table)
    {
        if(!isset($this->_table_schema[$table]))
        {
            $type_query = sprintf('select type from sqlite_master where name = "%s";', $table);
            $type = $this->execute($type_query, KDatabase::RESULT_USE)->fetchColumn();

            $schema              = new KDatabaseSchemaTable();
            $schema->name        = $table;
            $schema->type        = $type === 'view' ? 'VIEW' : 'BASE';

            $this->_table_schema[$table] = $schema;

            $query = sprintf('PRAGMA table_info([%s]);', $table);
            $table_info = $this->execute($query, KDatabase::RESULT_USE)->fetchAll(\PDO::FETCH_OBJ);

            $index_query = sprintf('PRAGMA index_list(%s);', $table);
            $index_info = $this->execute($index_query, KDatabase::RESULT_USE)->fetchAll(\PDO::FETCH_OBJ);
            $indexes = [];

            foreach($index_info as $index) {
                $index->columns = [];

                $detailed_info = $this->execute(sprintf('PRAGMA index_xinfo(%s);', $index->name), KDatabase::RESULT_USE)->fetchAll(\PDO::FETCH_ASSOC);
                array_pop($detailed_info); // remove the last element where cid = -1

                if(count($detailed_info) === 1 && $index->unique === '1') {
                    $index->columns = [$detailed_info[0]['name']];
                }

                if(count($detailed_info) > 1 && $index->unique) {
                    $index->columns = array_column($detailed_info, 'name');
                }

                $indexes[$index->name] = $index;
            }

            $columns = [];
            foreach ($table_info as $column_info) {
                $type = strtolower($column_info->type);

                $default = $column_info->dflt_value;

                if ($default && $default[0] === "'") {
                    $default = substr($default, 1, strlen($default)-2);
                } elseif (str_contains($default, '.')) {
                    $default = (float) $default;
                } elseif (is_numeric($default)) {
                    $default = (int) $default;
                }

                $column           = new KDatabaseSchemaColumn();
                $column->Table    = $table;
                $column->name     = $column_info->name;
                $column->type     = $type;
                $column->default  = $default;
                $column->required = $column_info->notnull != 0;
                $column->primary  = $column_info->pk == 1;
                $column->unique   = $column->primary;
                $column->autoinc  = $column_info->type === 'INTEGER' && $column->primary;
                $column->filter   = (isset($this->_type_map[$type]) ? $this->_type_map[$type] : 'raw');

                $columns[$column->name] = $column;

                if (!$column->unique) {
                    foreach ($indexes as $index) {
                        if (in_array($column->name, $index->columns)) {
                            if ($index->unique && $index->columns === 1) {
                                $column->unique = true;
                            } else {
                                $column->unique = true;
                                $column->related = array_diff($index->columns, [$column->name]);
                            }
                        }
                    }
                }
            }

            $this->_table_schema[$table]->indexes = $indexes;
            $this->_table_schema[$table]->columns = $columns;
        }

        return $this->_table_schema[$table];
    }

}
