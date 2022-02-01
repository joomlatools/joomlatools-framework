<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Folders Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelFolders extends ComFilesModelNodes
{
	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

		$this->getState()->insert('tree', 'boolean', false);
	}

    protected function _actionFetch(KModelContext $context)
    {
        $state = $this->getState();

        $folders = $this->getObject('com:files.adapter.iterator')->getFolders(array(
            'path'    => $this->getPath(),
            'recurse' => !!$state->tree,
            'filter'  => array($this, 'iteratorFilter'),
            'map'     => array($this, 'iteratorMap'),
            'sort'    => $state->sort
        ));

        if ($folders === false) {
            throw new UnexpectedValueException('Invalid folder');
        }

        $this->_count = count($folders);

        if (strtolower($state->direction) == 'desc') {
            $folders = array_reverse($folders);
        }

        $folders = array_slice($folders, $state->offset, $state->limit ? $state->limit : $this->_count);

        $identifier         = $this->getIdentifier()->toArray();
        $identifier['path'] = array('model', 'entity');
        $collection = $this->getObject($identifier);

        foreach ($folders as $folder)
        {
            $hierarchy = array();
            if ($state->tree)
            {
                $hierarchy = explode('/', dirname($folder));
                if (count($hierarchy) === 1 && $hierarchy[0] === '.') {
                    $hierarchy = array();
                }
            }

            $base = \Koowa\basename($folder);
            $name = strpos($folder, '/') !== false ? substr($folder, strrpos($folder, '/')+1) : $base;

            // Number of files for each folder
            $file_count = $this->getObject('com:files.model.files')
                ->container($state->container)
                ->folder($folder)
                ->count();

            $properties = array(
                'container'   => $state->container,
                'folder'      => $hierarchy ? implode('/', $hierarchy) : $state->folder,
                'name'        => $name,
                'hierarchy'   => $hierarchy,
                'file_count' => $file_count
            );

            $collection->create($properties);
        }

        return $collection;
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
		$path = str_replace('\\', '/', $path);

		if ($container = $this->getContainer()) {
            $path = str_replace($container->fullpath.'/', '', $path);
        }

		return $path;
	}

	public function iteratorFilter($path)
	{
        $state    = $this->getState();
		$filename = \Koowa\basename($path);

        if ($filename && $filename[0] === '.') {
            return false;
        }

		if ($state->name)
		{
            if (!in_array($filename, (array) $state->name) && !in_array(ComFilesFilterPath::normalizePath($filename), (array) $state->name)) {
                return false;
            }
		}

		if ($state->search && stripos($filename, $state->search) === false) {
			return false;
		}
	}
}
