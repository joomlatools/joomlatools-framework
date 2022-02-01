<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Import Controller Class.
 */
class ComMigratorControllerImport extends ComMigratorControllerMigrator
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors' => array('com:migrator.controller.behavior.uploadable'),
            'folder'    => JPATH_ROOT . '/tmp/migrator_import',
            'importers' => array()
        ));

        $config->view = 'com:migrator.view.import.'.$this->getObject('request')->getFormat();

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        $request->getHeaders()->set('X-Flush-Response', 1);

        return $request;
    }

    protected function _actionRun(KControllerContextInterface $context)
    {
        $request = $this->getRequest();
        $job    = $request->getQuery()->job;

        /** @var ComMigratorMigratorImportAbstract $importer */
        $importer = $this->getImporter($request->getQuery()->extension);

        if ($importer->hasJob($job))
        {
            $result = $importer->run($job);

            $context->response->setStatus($importer->getResponse()->getStatusCode());
            $context->response->setContent($importer->getResponse()->getContent());
        }
        else {
            throw new RuntimeException('Invalid job');
        }
    }

    public function getImporters()
    {
        if (empty($this->_importers)) {
            $importers = array();

            foreach ($this->getConfig()->importers as $extension => $identifier)
            {
                $config = array('extension' => $extension);

                if ($importer = $this->_createImporter($identifier, $config)) {
                    $importers[$extension] = $importer;
                }
            }

            $this->_importers = $importers;
        }

        return $this->_importers;
    }

    public function getImporter($extension)
    {
        $importers = $this->getImporters();

        if (!isset($importers[$extension])) {
            throw new RuntimeException('Importer not found for '.$extension);
        }

        return $importers[$extension];
    }

    protected function _createImporter($identifier, $config = array())
    {
        if (isset($config['extension'])) {
            $config['version'] = $this->getVersion($config['extension']);
        }

        $config['folder']         = $this->getTemporaryFolder();
        $config['request']        = $this->getRequest();
        $config['source_version'] = $this->getConfig()->source_version;

        if (strpos($identifier, '.') === false) {
            $identifier = 'com:migrator.migrator.import.'.$identifier;
        }

        $importer = $this->getObject($identifier, $config);

        return $importer;
    }

    public function getView()
    {
        $view = parent::getView();
        $view->extension = $this->getRequest()->getQuery()->extension;

        return $view;
    }
}