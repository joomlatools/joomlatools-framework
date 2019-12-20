<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Trashable Controller Behavior
 * Soft deletes entities
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Koowa\Library\Controller\Behavior
 */
class KControllerBehaviorTrashable extends KControllerBehaviorAbstract
{
    protected function _beforeBrowse(KControllerContextInterface $context)
    {
        if ($this->hasToolbar('actionbar')) {
            $this->getToolbar('actionbar')->decorate('lib:controller.toolbar.trashable');
        }
    }
    
    /**
     * Trash entities
     *
     * @param  KControllerContextInterfaceInterface $context
     * @return void
     */
    protected function _actionTrash(KControllerContextInterface $context)
    {
        $data             = $context->request->data;
        $data->trashed    = 1;
        $data->trashed_on = gmdate('Y-m-d H:i:s');
        $data->trashed_by = $context->user->getId();

        return $this->getMixer()->execute('edit', $context);
    }

    /**
     * Restore trashed entities
     *
     * @param KControllerContextInterface $context
     */
    protected function _actionRestore(KControllerContextInterface $context)
    {
        $translator = $this->getObject('translator');
        $context->response->addMessage($translator->translate('Trashed item(s) has been restored'));
        
        $data             = $context->request->data;
        $data->trashed    = 0;
        $data->trashed_on = null;
        $data->trashed_by = null;

        return $this->getMixer()->execute('edit', $context);
    }
}
