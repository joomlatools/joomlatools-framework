<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2019 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Trashable Model Behavior
 *
 * @author  Jebb Domingo <https://github.com/jebbdomingo>
 * @package Koowa\Library\Model\Behavior
 */
class KModelBehaviorTrashable extends KModelBehaviorAbstract
{
    public function onMixin(KObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        $mixer->getState()->insert('trashed', 'int', 0);
    }

    protected function _beforeFetch(KModelContextInterface $context)
    {
        $state = $context->getState();

        if (!is_null($state->trashed)) {
            $context->query->where('(tbl.trashed IN :state)')->bind(['state' => (array) $state->trashed]);
        }
    }

    protected function _beforeCount(KModelContextInterface $context)
    {
        $this->_beforeFetch($context);
    }
}