<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Files Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelFiles extends ComFilesModelNodes
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('behaviors' => array('thumbnailable')));

        parent::_initialize($config);
    }

    protected function _beforeFetch(KModelContext $context)
    {
        $context->local = true;

        $state = $this->getState();

        if ($uri = $state->uri)
        {
            $parts = parse_url($uri);

            if (isset($parts['scheme']) && $parts['scheme'] !== 'file') {
                $context->local = false;
            }
        }
    }

    protected function _actionFetch(KModelContext $context)
    {
        $state = $this->getState();

        if ($context->local)
        {
            $files = $this->getObject('com:files.adapter.iterator')->getFiles(array(
                'path'    => $this->getPath(),
                'exclude' => array('.svn', '.htaccess', 'web.config', '.git', 'CVS', 'index.html', '.DS_Store', 'Thumbs.db', 'Desktop.ini'),
                'filter'  => array($this, 'iteratorFilter'),
                'map'     => array($this, 'iteratorMap'),
                'sort'    => $state->sort
            ));

            if ($files === false) {
                throw new UnexpectedValueException('Invalid folder: ' . $state->folder);
            }
        }
        else $files = array($state->uri);


        $this->_count = count($files);

        if (strtolower($state->direction) == 'desc') {
            $files = array_reverse($files);
        }

        $results = array_slice($files, $state->offset ?: 0, $state->limit ? $state->limit : $this->_count);
        $files   = array();

        foreach ($results as $result) {
            $files[] = $context->local ? array('name' => $result) : array('uri' => $result);
        }

        $context->files = $files;

        if ($this->invokeCommand('before.createset', $context) !== false)
        {
            $context->set = $this->_actionCreateSet($context);
            $this->invokeCommand('after.createset', $context);
        }

        return $context->set;
    }

    protected function _actionCreateSet(KModelContext $context)
    {
        $state = $context->getState();

        $data = array();

        foreach ($context->files as $file)
        {
            if ($context->local)
            {
                $file->append(array(
                    'container' => $state->container,
                    'folder'    => $state->folder
                ));
            }

            $data[] = $file->toArray();
        }

        $identifier         = $this->getIdentifier()->toArray();
        $identifier['path'] = array('model', 'entity');

        return $this->getObject($identifier, array('data' => $data));
    }

    protected function _actionCount(KModelContext $context)
    {
        if (!isset($this->_count)) {
            $this->fetch();
        }

        return $this->_count;
    }

	public function iteratorMap($path)
	{
		return \Koowa\basename($path);
	}

	public function iteratorFilter($path)
	{
        $state     = $this->getState();
		$filename  = \Koowa\basename($path);
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($filename && $filename[0] === '.') {
            return false;
        }

        if ($state->name)
        {
            if (!in_array($filename, (array) $state->name) && !in_array(ComFilesFilterPath::normalizePath($filename), (array) $state->name)) {
				return false;
			}
		}

		if ($state->types)
        {
			if ((in_array($extension, ComFilesModelEntityFile::$extension_type_map['image']) && !in_array('image', (array) $state->types))
			|| (!in_array($extension, ComFilesModelEntityFile::$extension_type_map['image']) && !in_array('file', (array) $state->types))
			) {
				return false;
			}
		}

		if ($state->search && stripos($filename, $state->search) === false) {
            return false;
        }
	}
}
