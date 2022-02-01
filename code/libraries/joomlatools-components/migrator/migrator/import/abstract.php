<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Abstract Tasker Class.
 */
abstract class ComMigratorMigratorImportAbstract extends ComMigratorMigratorAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array(
                'com:migrator.migrator.behavior.import.database',
                'com:migrator.migrator.behavior.import.insert',
                //'com:migrator.migrator.behavior.import.tree'
            ),
            'folder' => ''
        ));

        parent::_initialize($config);
    }

    /**
     * Adds a job to the queue.
     *
     * @param      string $name   The job name.
     * @param      mixed  $config The job parameters.
     *
     * @return $this
     */
    public function addJob($name, $config)
    {
        $config = new KObjectConfig($config);
        $config->append(array(
            'action'    => 'import',
            'chunkable' => false,
            'folder'    => $this->getConfig()->folder
        ));

        return parent::addJob($name, $config);
    }
}