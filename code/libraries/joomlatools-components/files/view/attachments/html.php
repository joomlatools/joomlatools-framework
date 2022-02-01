<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachments Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesViewAttachmentsHtml extends ComKoowaViewHtml
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('auto_fetch' => false));
        parent::_initialize($config);
    }

    protected function _fetchData(KViewContext $context)
    {
        $state = $this->getModel()->getState();

        $container = $this->getObject('com:files.model.containers')->id($state->container)->fetch();

        $context->data->sitebase  = trim(JURI::root(), '/');
        $context->data->token     = $this->getObject('user')->getSession()->getToken();
        $context->data->container = $container->getIterator()->current();

        parent::_fetchData($context);

        $context->parameters->config = $this->getConfig()->config;
    }
}