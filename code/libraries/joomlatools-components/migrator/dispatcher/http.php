<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComMigratorDispatcherHttp extends ComKoowaDispatcherHttp
{
    // FIXME: this is here because forwarded dispatchers still render results
    protected function _actionSend(KDispatcherContextInterface $context)
    {
        if (!$context->getRequest()->isGet() || $context->getResponse()->getContentType() !== 'text/html') {
            return parent::_actionSend($context);
        }
    }
}
