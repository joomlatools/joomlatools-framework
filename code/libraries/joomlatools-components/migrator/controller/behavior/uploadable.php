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

        if ($folder && file_exists($file)) {
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

        if (empty($context->file)) {
            throw new RuntimeException('Cannot find uploaded file');
        }

        $this->createTemporaryFolder();
    }

    /**
     * Uploads and extracts the migration file
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionUpload(KControllerContextInterface $context)
    {
        $zip  = new ZipArchive();

        if ($zip->open($context->file) !== true)
        {
            $this->deleteTemporaryFolder();

            throw new RuntimeException("Cannot open uploaded file");
        }

        $files = $this->_getExtractableFiles($zip);

        if (!$files || !$zip->extractTo($this->getTemporaryFolder(), $files))
        {
            $zip->close();
            $this->deleteTemporaryFolder();

            throw new RuntimeException('Unable to extract uploaded file');
        }

        $zip->close();

        try {
            $this->_setConfig($context);
        }
        catch (RuntimeException $e)
        {
            $this->deleteTemporaryFolder();

            throw $e;
        }

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
     * Returns the files of a package that importers read, i.e. the CSV and JSON files at its root.
     *
     * @param ZipArchive $zip The package.
     * @return array The file names.
     */
    protected function _getExtractableFiles(ZipArchive $zip)
    {
        $files = array();

        for ($i = 0; $i < $zip->numFiles; $i++)
        {
            $name = $zip->getNameIndex($i);

            if (preg_match('/^[\w\-]+\.(csv|json)$/', $name)) {
                $files[] = $name;
            }
        }

        return $files;
    }
}
