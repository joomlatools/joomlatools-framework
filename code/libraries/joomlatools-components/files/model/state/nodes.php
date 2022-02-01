<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Nodes Model State
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelStateNodes extends KModelState
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->insert('uri', 'url');
    }

    public function set($name, $value = null)
    {
        if ($name == 'uri')
        {
            $parts = $this->getObject('com:files.model.state.parser.url')->parse($value);

            if (!$parts->scheme || $parts->scheme == 'file')
            {
                $this->set('name', basename($parts->path));

                $folder = dirname($parts->path);

                if ($container = $parts->container)
                {
                    $this->set('container', $container);

                    // Folder is relative to container
                    $folder = trim($folder, '/');
                }

                 $this->set('folder', $folder);
            }
        }

        return parent::set($name, $value);
    }
}