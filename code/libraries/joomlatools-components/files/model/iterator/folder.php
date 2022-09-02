<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Folder Iterator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Files
 */
class ComFilesModelIteratorFolder extends \RecursiveIteratorIterator
{
    public function __construct(ComFilesModelEntityFolders $nodes, $mode = \RecursiveIteratorIterator::SELF_FIRST, $flags = 0)
    {
        parent::__construct($nodes, $mode, $flags);
    }

    public function callGetChildren()
    {
        return $this->current()->getChildren()->getIterator();
    }

    public function callHasChildren()
    {
        return $this->current()->hasChildren();
    }
}
