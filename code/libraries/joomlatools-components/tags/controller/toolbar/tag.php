<?php
/**
 * Tagging component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */

/**
 * Tag Controller Toolbar
 *
 * @author  Tom Janssens <http://github.com/tomjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsControllerToolbarTag extends KControllerToolbarActionbar
{
    /**
     * New tag toolbar command
     *
     * @param KControllerToolbarCommand $command
     */
    protected function _commandNew(KControllerToolbarCommand $command)
    {
        $component = $this->getController()->getIdentifier()->package;
        $view      = KStringInflector::singularize($this->getIdentifier()->name);

        $command->href = 'component='.$component.'&view='.$view;
    }
}