<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Uploadable Controller Behavior Class.
 */
class ComMigratorControllerBehaviorUploadable extends KControllerBehaviorAbstract
{
    protected function _setConfig(KControllerContextInterface $context)
    {
        $folder = $this->getTemporaryFolder();
        $file   = $folder . '/export.json';

        if (file_exists($file)) {
            $config = json_decode(trim(file_get_contents($file)));
        } else {
            throw new RuntimeException('JSON export file not found');
        }

        $this->getMixer()->getConfig()->extension      = $config->extension->name;
        $this->getMixer()->getConfig()->source_version = $config->extension->version;
    }

    protected function _beforeRun(KControllerContextInterface $context)
    {
        $this->_setConfig($context);
    }

    /**
     * Some pre-upload checks.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _beforeUpload(KControllerContextInterface $context)
    {
        if (!$context->request->files->has('file')) {
            throw new RuntimeException('Uploaded file not found in request');
        }

        $context->file = $context->request->files->file['tmp_name'];

        $folder = $this->getTemporaryFolder();

        if (!is_writable(dirname($folder))) {
            throw new RuntimeException('Please make sure your Joomla tmp directory in your site root is writable');
        }

        if (empty($context->file)) {
            throw new RuntimeException('Cannot find uploaded file');
        }

        if (file_exists($folder)) {
            $this->_deleteFolder($folder);
        }
    }

    /**
     * Uploads and extracts the migration file
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionUpload(KControllerContextInterface $context)
    {
        $zip  = new ZipArchive();

        if ($zip->open($context->file) !== true) {
            throw new RuntimeException("Cannot open uploaded file");
        }

        if (!$zip->extractTo($this->getTemporaryFolder())) {
            throw new RuntimeException('Unable to extract uploaded file');
        }

        $zip->close();

        $this->_setConfig($context);

        // Send a task list back to the client.
        if ($importer = $this->getImporter($this->getMixer()->getConfig()->extension))
        {
            $jobs = array();
            foreach ($importer->getIterator() as $job) {
                $jobs[$job->name] = KObjectConfig::unbox($job);
            }

            $output = array(
                'extension' => $this->getMixer()->getConfig()->extension,
                'jobs'      => $jobs,
                'status'    => true
            );
        }
        else throw new RuntimeException('Importer not found');

        return new KObjectConfigJson($output);
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