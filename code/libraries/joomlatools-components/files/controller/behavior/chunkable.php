<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Chunkable Controller Behavior
 *
 * Saves uploaded files in chunks before passing it to the entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesControllerBehaviorChunkable extends KControllerBehaviorAbstract
{
    /**
     * A reference to the uploaded file in .tmp directory
     *
     * @var string
     */
    protected $_temporary_file;

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'   => self::PRIORITY_HIGH
        ));

        parent::_initialize($config);
    }

    /**
     * Gathers file chunks into a file in the .tmp directory
     *
     * @param KControllerContextInterface $context
     * @return bool
     * @throws KControllerExceptionActionFailed
     */
    protected function _beforeAdd(KControllerContextInterface $context)
    {
        $request = $context->request;

        if (!$request->data->has('chunk') || !$request->data->has('name')) {
            return true;
        }

        if (!$request->files->has('file') || $request->files->file['error']) {
            throw new KControllerExceptionRequestInvalid('Chunk has no file');
        }

        $chunk  = $request->data->get('chunk', 'int');
        $chunks = $request->data->get('chunks', 'int');
        $name   = $request->data->get('name', 'string');

        // Run filename validation for chunk 0
        if ($chunk === 0)
        {
            $context->request->data->file = $context->request->files->file['tmp_name'];

            $entity = $this->getModel()->create($context->request->data->toArray());

            $filter = $this->getObject('com:files.filter.file.name');
            $result = $filter->validate($entity);

            if ($result === false)
            {
                $errors = $filter->getErrors();
                if (count($errors)) {
                    throw new KControllerExceptionActionFailed(array_shift($errors));
                }
            }
        }

        $folder = $this->getModel()->getContainer()->fullpath.'/.tmp';
        if (!is_dir($folder))
        {
            $result = mkdir($folder, 0755);

            if (!$result || !is_dir($folder)) {
                throw new KControllerExceptionActionFailed('Unable to create tmp directory');
            }
        }

        $this->_temporary_file = $folder.'/'.$name;

        $output = @fopen($this->_temporary_file.'.part', $chunk == 0 ? 'wb' : 'ab');
        $input  = @fopen($request->files->file['tmp_name'], "rb");

        if (!$input || !$output) {
            throw new KControllerExceptionActionFailed('Unable to open i/o files');
        }

        while ($buffer = fread($input, 4096)) {
            fwrite($output, $buffer);
        }

        @fclose($input);
        @fclose($output);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1)
        {
            // Strip the temp .part suffix off
            rename($this->_temporary_file.'.part', $this->_temporary_file);

            $context->request->data->file = new SplFileObject($this->_temporary_file);
        }
        else
        {
            $data = array(
                'status' => true
            );

            $context->response->setContent(json_encode($data), 'application/json');

            return false;
        }
    }

    protected function _beforeEdit(KControllerContextInterface $context)
    {
        $this->_beforeAdd($context);
    }

    /**
     * Removes the temporary file after upload
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterAdd(KControllerContextInterface $context)
    {
        if ($this->_temporary_file && is_file($this->_temporary_file)) {
            @unlink($this->_temporary_file);
        }
    }

    protected function _afterEdit(KControllerContextInterface $context)
    {
        $this->_afterAdd($context);
    }

}