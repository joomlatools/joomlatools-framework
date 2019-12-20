<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2019 and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Controller Toolbar Trashable
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Koowa\Library\Controller\Toolbar
 */
class KControllerToolbarTrashable extends KControllerToolbarDecorator
{
    protected function _afterBrowse(KControllerContextInterface $context)
    {
        $controller = $this->getController();

        if ($controller->getRequest()->getQuery()->get('trashed', 'int')) {
            $this->addCommand('restore', array('allowed' => $controller->canAdd()));
        } else {
            $this->addCommand('trash', array('allowed' => $controller->canDelete()));
        }
    }

    /**
     * Trash command
     *
     * @param KControllerToolbarCommand $command A KControllerToolbarCommand object
     */
    protected function _commandTrash(KControllerToolbarCommand $command)
    {
        $translator = $this->getObject('translator');
        $command->label = $translator->translate('Trash');
        $command->append(array(
        'attribs' => array(
            'data-action' => 'trash',
            'data-prompt' => $translator->translate('Are you sure you want to trash the selected item(s)?')
            )
        ));

        $command->icon  = 'k-icon-trash';
    }
    
    /**
     * Restore command
     *
     * @param KControllerToolbarCommand $command A KControllerToolbarCommand object
     */
    protected function _commandRestore(KControllerToolbarCommand $command)
    {
        $translator = $this->getObject('translator');
        $query      = $this->getController()->getRequest()->getQuery();

        $command->icon  = 'k-icon-action-undo';
        $command->label = $translator->translate('Restore from trash');
        $command->append(array(
            'attribs' => array(
                'data-action' => 'restore',
            )
        ));
    }
}