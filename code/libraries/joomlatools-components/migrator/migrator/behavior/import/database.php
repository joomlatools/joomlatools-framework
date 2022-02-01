<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */


class ComMigratorMigratorBehaviorImportDatabase extends KControllerBehaviorAbstract
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
            'import_limit' => 20,
        ));

        parent::_initialize($config);
    }

    /**
     * Query task handler.
     *
     * Runs database queries from either a string or a dump file
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return boolean Result
     */
    protected function _actionQuery(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $result = false;

        if ($job->dump)
        {
            if (!is_file($job->dump)) {
                $job->dump = JPATH_ROOT.'/'.$job->dump;
            }

            $result = $this->executeDumpFile($job->dump);
        }
        else if ($job->query) {
            $result = $this->executeQuery($job->query);
        }

        return $result;
    }

    /**
     * Executes a dump file.
     *
     * @param string $file The file path of the dump file.
     *
     * @return bool False if there's an error, true otherwise.
     */
    public function executeDumpFile($file)
    {
        $result = false;

        if (file_exists($file)) {
            $result = $this->executeQuery(file_get_contents($file));
        }

        return $result;
    }

    /**
     * Executes a string with multiple database queries
     *
     * @param  string $query
     * @return boolean
     */
    public function executeQuery($query)
    {
        $adapter    = $this->getObject('lib:database.adapter.mysqli');
        $query      = $adapter->replaceTableNeedle($query);
        $connection = $adapter->getConnection();

        $result = $connection->multi_query($query);

        if ($result) {
            while ($connection->more_results() && $connection->next_result());
        }

        return $result;
    }

    /**
     * Fetches table rows.
     *
     * @param string $table  The table name.
     * @param array  $config The query configuration.
     *
     * @return mixed The fetched rows.
     */
    protected function _fetch($table, $config = array())
    {
        $config = new KObjectConfig($config);

        $config->append(array(
            'limit'  => 0,
            'offset' => 0,
            'where'  => array(),
            'result' => KDatabase::FETCH_ARRAY_LIST
        ));

        if (!$config->columns) {
            $config->columns = '*';
        }

        $query = $this->getObject('lib:database.query.select')
            ->table($table)
            ->columns(KObjectConfig::unbox($config->columns));

        foreach ($config->where as $column => $value) {
            $query->where("{$column} = :{$column}")->bind(array($column => $value));
        }

        if ($limit = $config->limit) {
            $query->limit($limit, $config->offset);
        }

        if ($pk = $this->_getPrimaryKey($table)) {
            $query->order($pk);
        }

        return $this->getObject('lib:database.adapter.mysqli')->select($query, $config->result);
    }

    /**
     * Copy task handler.
     *
     * Makes an identical copy of a table contents into another table.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionCopy(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'operation' => 'INSERT IGNORE'
        ));

        $source  = $job->source;
        $target  = $job->target;
        $columns = '*';

        // Truncate the target table before copying.
        if ($job->truncate) {
            $this->_truncateTable($target);
        }

        if ($job->skip_primary_key)
        {
            $table = $this->getObject('lib:database.table.default', array(
                'name' => $target
            ));
            $columns = array_diff(array_keys($table->getColumns()), array_keys($table->getPrimaryKey()));
        }

        $select = $this->getObject('lib:database.query.select')->table($source)->columns($columns);
        $query  = $this->getObject('lib:database.query.insert')->table($target)->values($select);

        if ($columns !== '*') {
            $query->columns($columns);
        }

        $count = 1;
        $query = str_replace('INSERT', $job->operation, $query->toString(), $count);

        $result = $this->getObject('lib:database.adapter.mysqli')->execute($query);

        return $result;
    }

    /**
     * Move task handler.
     *
     * Makes an identical copy of a table contents into another table.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionMove(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());

        $source = $job->source;
        $target = $job->target;

        $this->_dropTable($target);

        return $this->executeQuery(sprintf('RENAME TABLE #__%s TO #__%s', $source, $target));

    }

    protected function _actionImport_menu(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'source' => null,
            'type'   => null
        ));

        // Create the menu type if it does not exist
        if ($job->type)
        {
            $menu_type = $this->getObject('com:migrator.database.table.default', array(
                'name' => 'menu_types'
            ))->select(array('menutype' => $job->type), KDatabase::FETCH_ROW);

            if ($menu_type->isNew())
            {
                $menu_type->menutype = $job->type;
                $menu_type->title = 'Migrated menu items';
                $menu_type->save();
            }
        }

        // We need to make sure the aliases are different for each entry
        // since we will put them all under the same menu
        $sql = /** @lang text */
        "SET @alias := 0;

        UPDATE #__%1\$s
        SET alias = CONCAT(alias, (@alias := @alias + 1))
        WHERE id IN (SELECT id FROM
                       (SELECT DISTINCT(m1.id) FROM #__%1\$s AS m1, #__%1\$s AS m2
                       WHERE m1.alias = m2.alias AND m1.id > m2.id AND m1.language = m2.language) AS temporary)
        ";

        $this->executeQuery(sprintf($sql, $job->source));

        // Update component IDs
        $sql = /** @lang text */
        "UPDATE #__%1\$s AS m
        LEFT JOIN j_extensions AS e ON e.element = CONCAT('com_', SUBSTRING_INDEX(SUBSTRING_INDEX(m.link, 'com_', -1), '&', 1)) AND e.type = 'component'
        SET m.component_id = e.extension_id
        ";

        $this->executeQuery(sprintf($sql, $job->source));

        $table = $this->getObject('com:migrator.database.table.'.$job->source, array(
            'name' => $job->source
        ));
        $items = $table->select(array(), KDatabase::FETCH_ROWSET);

        foreach ($items as $item)
        {
            $data = $item->toArray();

            $old_id = (int) $data['id'];

            if ($job->type) {
                $data['menutype'] = $job->type;
            }

            $menu = JTable::getInstance('Menu');

            try
            {
                $query = $this->getObject('lib:database.query.select')
                              ->table('menu')
                              ->where('id = :id')
                              ->bind(array('id' => $old_id));

                $count = $table->count($query);

                // Check if the current menu item ID is available.
                if (!$count)
                {
                    $query = $this->getObject('lib:database.query.insert')
                                  ->table('menu')
                                  ->columns(array_keys($data))
                                  ->values(array_values($data));

                    $adapter = $this->getObject('lib:database.adapter.mysqli');

                    if ($adapter->insert($query)) {
                        $menu->load($adapter->getInsertId());
                    }

                }

                if (!$menu->id)
                {
                    unset($data['id']);
                    unset($data['parent_id']);
                    unset($data['path']);
                    unset($data['level']);
                    unset($data['lft']);
                    unset($data['rgt']);
                    unset($data['home']);

                    $menu->bind($data);
                }

                if ($menu->check())
                {
                    $menu->setLocation(1, 'last-child');

                    if ($menu->store())
                    {
                        $new_id = $menu->id;

                        if ($new_id != $old_id)
                        {
                            // Store info about mapping.
                            $params = array();

                            if ($item->params) {
                                $params = json_decode($item->params, true);
                            }

                            $params['migrator_data'] = array('menu_item_id' => $new_id);

                            $item->params = json_encode($params);
                            $item->save();
                        }
                    }
                }
            }
            catch (RuntimeException $e) {}
        }

        return true;
    }

    protected function _actionImport_settings(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'source' => 'extensions',
            'format' => 'csv',
            'merge'  => false
        ));

        $result = true;

        $file = new SplFileObject(sprintf('%s/%s.%s', $job->folder, $job->source, $job->format));
        $data = $this->_convertCsvtoArray($file);

        foreach ($data as $extension)
        {
            if (!isset($extension['name']) || !isset($extension['params'])) {
                throw new UnexpectedValueException('CSV format is different than expected');
            }

            $entity = $this->_fetch('extensions', array(
                'where'  => array(
                    'type'    => 'component',
                    'element' => $extension['name']
                ),
                'result' => KDatabase::FETCH_OBJECT
            ));

            if (!$entity) {
                throw new RuntimeException(sprintf('Could not find %s in the extensions table', $extension['name']));
            }

            if ($job->merge) {
                $params = json_encode(array_merge(json_decode($entity->params, true), json_decode($extension['params'], true)));
            }
            else $params = $extension['params'];

            $custom_data = isset($extension['custom_data']) ? $extension['custom_data'] : '';

            $query = $this->getObject('lib:database.query.update')
                ->table('extensions')
                ->where('extension_id = :extension_id')
                ->values('params = :params')
                ->values('custom_data = :custom_data')
                ->bind(array(
                    'extension_id' => $entity->extension_id,
                    'custom_data'  => $custom_data,
                    'params' => $params
                ));

            $result = $this->getObject('lib:database.adapter.mysqli')->update($query);
        }

        return $result;
    }

    /**
     * Reads a CSV file into an associative array
     *
     * Only use when you know beforehand that the CSV file is small in size and can fit into memory!
     *
     * @param SplFileObject $file
     * @return array
     */
    protected function _convertCsvtoArray(SplFileObject $file)
    {
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '"');

        $headers = array();
        $data    = array();
        foreach ($file as $i => $row)
        {
            if ($i === 0) {
                $headers = str_replace("\xEF\xBB\xBF", '', $row);

                continue;
            } else {
                $data[] = array_combine($headers, $row);
            }
        }

        return $data;
    }

    /**
     * Import task handler.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionImport(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'data' => array(
                'offset' => (int) $this->getRequest()->getData()->offset
            )
        ));

        $source = $job->source;
        $offset = $job->data->offset;
        $table  = $job->table;
        $limit  = $job->import_limit;

        // Truncate the target table when starting an import.
        if (!$offset) {
            $this->_truncateTable($job->table);
        }

        $rows = $this->_fetch($source, array('offset' => $offset, 'limit' => $limit));

        foreach ($rows as $data)
        {
            $method = sprintf('_convert%sData', ucfirst($job->entity));

            // Use a data converter if any.
            if (method_exists($this, $method)) {
                $data = $this->$method($data);
            }

            $entity = $this->_getEntity($job->entity, $data);

            // Save the entity.
            $entity->save();
        }

        $total     = $this->_countTotal($table);
        $completed = count($rows);
        $remaining = $total - $offset - $completed;
        $offset    = $offset + $limit;

        $output = array(
            'completed' => $completed,
            'total'     => $total,
            'remaining' => $remaining,
            'offset'    => $offset
        );

        return $output;
    }

    /**
     * Import assets task handler.
     *
     * @param ComMigratorMigratorContext $context
     *
     * @return array The task output.
     */
    protected function _actionImport_assets(ComMigratorMigratorContext $context)
    {
        $job = $context->getJob();
        $job->append($this->getConfig());
        $job->append(array(
            'extension' => null,
            'target'    => 'assets',
            'source'    => 'migrator_tmp_assets'
        ));

        $sql = /** @lang text */<<<SQL
        SET @parentID := (SELECT id FROM #__%2\$s WHERE name = '%1\$s');
        SET @rgt      := (SELECT rgt FROM #__%2\$s WHERE name = '%1\$s');
        SET @lft      := (SELECT lft FROM #__%2\$s WHERE name = '%1\$s');
        SET @lftTmp   := (SELECT lft FROM #__%3\$s WHERE name = '%1\$s');
        SET @delta    := (@lft - @lftTmp);
        SET @width    := (@rgt - @lft - 1);
        SET @newWidth := (SELECT 2*(COUNT(*)-1)-@width FROM #__%3\$s WHERE name LIKE '%1\$s%%');

        # delete old assets
        DELETE FROM #__%2\$s WHERE name LIKE '%1\$s.%%';

        # make space for the new assets
        UPDATE #__%2\$s SET lft = lft + @newWidth WHERE lft > @rgt;
        UPDATE #__%2\$s SET rgt = rgt + @newWidth WHERE rgt >= @rgt;

        # update the temporary table with correct parent_id, lft, and rgt values
        UPDATE #__%3\$s SET parent_id = @parentID, lft = (lft + @delta), rgt = (rgt + @delta);

        # move the rules for the main component entry first
        UPDATE #__%2\$s SET rules = (SELECT rules FROM #__%3\$s WHERE name = '%1\$s') WHERE name = '%1\$s';

        # move the rest of the data
        REPLACE INTO #__%2\$s (parent_id, lft, rgt, level, name, title, rules)
            SELECT parent_id, lft, rgt, level, name, title, rules FROM #__%3\$s WHERE name LIKE '%1\$s.%%';

        # drop temporary table
        DROP TABLE IF EXISTS `#__%3\$s`;
SQL;

        $sql_asset_id = /** @lang text */<<<SQL
        UPDATE #__%1\$s AS tbl
        JOIN #__%4\$s AS assets ON assets.name = CONCAT('%2\$s.', tbl.%3\$s)
        SET tbl.asset_id = assets.id;

SQL;

        $sql = sprintf($sql, $job->extension, $job->target, $job->source);

        $tables = (array) KObjectConfig::unbox($job->tables);

        if ($tables)
        {
            foreach ($tables as $table) {
                $sql .= sprintf($sql_asset_id, $table[0], $table[1], $table[2], $job->target);
            }
        }

        $result = $this->executeQuery($sql);

        $output = array(
            'result' => $result
        );

        return $output;
    }

    /**
     * Entity getter.
     *
     * @param string $name The name of the entity.
     * @param array  $data
     *
     * @return KModelEntityInterface The entity.
     */
    protected function _getEntity($name, $data)
    {
        $name      = KStringInflector::pluralize($name);
        $extension = $this->getConfig()->extension;

        $model = $this->getObject(sprintf('com://admin/%s.model.%s', $extension, $name));

        return $model->create($data);
    }

    /**
     * Counts the total number of rows in a table.
     *
     * @param string $table      The table name.
     * @param array  $conditions An associative array containing conditions.
     *
     * @return int The number of rows.
     */
    protected function _countTotal($table, $conditions = array())
    {
        $query = $this->getObject('lib:database.query.select')->table($table)->columns('COUNT(*)');

        foreach ($conditions as $column => $value) {
            $query->where(sprintf('%1\$s = :%1\$s', $column))->bind(array($column => $value));
        }

        return $this->getObject('lib:database.adapter.mysqli')->select($query, KDatabase::FETCH_FIELD);
    }

    /**
     * Truncates a table.
     *
     * @param string $table The table name to truncate.
     */
    protected function _truncateTable($table)
    {
        $adapter = $this->getObject('lib:database.adapter.mysqli');
        $queries = [
            'SET FOREIGN_KEY_CHECKS = 0',
            sprintf('TRUNCATE TABLE `%s%s`', JFactory::getDbo()->getPrefix(), $table),
            'SET FOREIGN_KEY_CHECKS = 1',
        ];

        foreach ($queries as $query) {
            $adapter->execute($query);
        }
    }

    /**
     * Drops a table.
     *
     * @param string $table The table name to truncate.
     */
    protected function _dropTable($table)
    {
        $adapter = $this->getObject('lib:database.adapter.mysqli');
        $queries = [
            'SET FOREIGN_KEY_CHECKS = 0',
            sprintf('DROP TABLE IF EXISTS `%s%s`', JFactory::getDbo()->getPrefix(), $table),
            'SET FOREIGN_KEY_CHECKS = 1',
        ];

        foreach ($queries as $query) {
            $adapter->execute($query);
        }
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
}
