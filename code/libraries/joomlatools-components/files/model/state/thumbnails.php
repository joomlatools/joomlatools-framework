<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Thumbnails Model State
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelStateThumbnails extends KModelState
{
    protected $_source_file;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->insert('version', 'cmd')
             ->insert('source', 'url');
    }

    public function set($name, $value = null)
    {
        if ($name == 'source')
        {
            if ($this->get($name) != $value) {
                $this->_source_file = null; // Reset source file if source gets changed
            }

            $parts = $this->getObject('com:files.model.state.parser.url')->parse($value);

            $this->set('name', basename($parts->path) . '.jpg');
            $this->set('folder', trim(dirname($parts->path), '/'));
        }

        return parent::set($name, $value);
    }

    public function remove($name)
    {
        if ($name == 'source') {
            $this->_source_file = null;
        }

        return parent::remove($name);
    }

    public function reset($default = true)
    {
        $this->_source_file = null;

        return parent::reset($default);
    }

    public function getSourceFile()
    {
        if (!$this->_source_file && ($source = $this->get('source')))
        {
            $file = $this->getObject('com:files.model.files')->uri($source)->fetch();

            if (!$file->isNew()) {
                $this->_source_file = $file;
            }
        }

        return $this->_source_file;
    }
}