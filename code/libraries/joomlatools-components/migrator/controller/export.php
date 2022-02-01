<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */


class ComMigratorControllerExport extends ComMigratorControllerMigrator
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'folder'    => JPATH_ROOT . '/tmp/migrator_export',
            'exporters' => array()
        ));

        $config->view = 'com:migrator.view.export.'.$this->getObject('request')->getFormat();

        parent::_initialize($config);
    }

    public function getRequest()
    {
        $request = parent::getRequest();

        $request->getHeaders()->set('X-Flush-Response', 1);

        return $request;
    }

    protected function _actionRender(KControllerContextInterface $context)
    {
        if ($this->getRequest()->getFormat() === 'binary')
        {
            $folder  = $this->getTemporaryFolder();
            $package = sprintf('%s/export.zip', $folder);

            $this->getResponse()
                ->attachTransport('stream')
                ->setContent($package, 'application/octet-stream');
        }
        else {
            return parent::_actionRender($context);
        }
    }

    public function getView()
    {
        $view = parent::getView();
        $view->exporters = $this->getExporters();

        return $view;
    }

    protected function _actionCleanup(KControllerContextInterface $context)
    {
        $folder = $this->getTemporaryFolder();

        if (file_exists($folder) && !$this->_deleteFolder($folder)) {
            throw new RuntimeException('Unable to delete the export folder');
        }

        if (!mkdir($folder, 0755, true)) {
            throw new RuntimeException('Export folder could not be created');
        }

        if (!is_writable($folder)) {
            throw new RuntimeException('The export folder is not writtable');
        }

        $context->response->setContent(json_encode(array('status' => true)));
    }

    protected function _actionRun(KControllerContextInterface $context)
    {
        $request = $this->getRequest();
        $job    = $request->getQuery()->job;

        /** @var ComMigratorMigratorExportAbstract $exporter */
        $exporter = $this->getExporter($request->getQuery()->extension);

        if ($exporter->hasJob($job))
        {
            $result = $exporter->run($job);

            $context->response->setStatus($exporter->getResponse()->getStatusCode());
            $context->response->setContent($exporter->getResponse()->getContent());
        }
        else {
            throw new RuntimeException('Invalid job');
        }
    }

    protected function _actionPackage(KControllerContextInterface $context)
    {
        $folder    = $this->getTemporaryFolder();
        $package   = sprintf('%s/export.zip', $folder);
        $extension = $this->getRequest()->getQuery()->extension;

        if (file_exists($package)) {
            unlink($package);
        }

        $iterator = new DirectoryIterator($folder);

        $files = array();

        foreach ($iterator as $node)
        {
            if ($node->isFile()) {
                $files[] = $node->getPathName();
            }
        }

        $zip = new ZipArchive();

        if ($zip->open($package, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Cannot create the ZIP export file');
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $metadata = array(
            'extension' => array(
                'name'     => $extension,
                'version' =>  $this->getVersion($extension)
            ),
            'joomla'    => JVERSION,
            'migrator'  => ComMigratorVersion::VERSION,
            'date'      => gmdate("Y-m-d H:i:s", time())
        );

        $zip->addFromString('export.json', json_encode($metadata));

        $zip->close();

        $context->response->setContent(json_encode(array('status' => true)));
    }

    public function getExporters()
    {
        if (empty($this->_exporters)) {
            $exporters = array();

            foreach ($this->getConfig()->exporters as $extension => $identifier)
            {
                $config = array('extension' => $extension);

                if ($exporter = $this->_createExporter($identifier, $config)) {
                    $exporters[$extension] = $exporter;
                }
            }

            $this->_exporters = $exporters;
        }

        return $this->_exporters;
    }

    public function getExporter($extension)
    {
        $exporters = $this->getExporters();

        if (!isset($exporters[$extension])) {
            throw new RuntimeException('Exporter not found for '.$extension);
        }

        return $exporters[$extension];
    }

    protected function _createExporter($identifier, $config = array())
    {
        if (isset($config['extension'])) {
            $config['version'] = $this->getVersion($config['extension']);
        }

        $config['folder'] = $this->getTemporaryFolder();
        $config['request'] = $this->getRequest();

        if (strpos($identifier, '.') === false) {
            $identifier = 'com:migrator.migrator.export.'.$identifier;
        }

        $exporter = $this->getObject($identifier, $config);

        return $exporter;
    }

    /**
     * Recursively deletes a directory with all of its contents.
     *
     * @param string $folder The folder to delete.
     *
     * @return bool True is successful, false otherwise.
     */
    protected function _deleteFolder($folder)
    {
        $files = array_diff(scandir($folder), array('.', '..'));

        foreach ($files as $file)
        {
            $node = sprintf('%s/%s', $folder, $file);

            if (is_dir($node)) {
                $this->_deleteFolder($node);
            }
            else unlink($node);
        }

        return rmdir($folder);
    }

}