<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Decoratable Dispatcher Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Behavior
 */
class ComKoowaDispatcherBehaviorDecoratable extends KControllerBehaviorAbstract
{
    /**
     * Check if the behavior is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $mixer   = $this->getMixer();
        $request = $mixer->getRequest();

        if($request->isGet() && $request->getFormat() == 'html' && !$request->isAjax()) {
            return parent::isSupported();
        }

        return false;
    }

    /**
     * Set the Joomla application context
     *
     * @param   KDispatcherContextInterface $context A command context object
     * @return 	void
     */
    protected function _beforeDispatch(KDispatcherContextInterface $context)
    {
        $request = $context->getRequest();

        if ($this->getDecorator() != 'joomla')
        {
            $app = JFactory::getApplication();

            if ($app->isSite()) {
                $app->setTemplate('system');
            }
        }
    }

    /**
     * Decorate the response
     *
     * @param   KDispatcherContextInterface $context A command context object
     * @return 	void
     */
    protected function _beforeSend(KDispatcherContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        if(!$response->isDownloadable())
        {
            //Render the page
            $this->getObject('com:koowa.controller.page',  array('response' => $response))
                ->layout($this->getDecorator())
                ->render();
        }
    }

    /**
     * Pass the response to Joomla
     *
     * @param   KDispatcherContextInterface $context A command context object
     * @return 	bool
     */
    protected function _beforeFlush(KDispatcherContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        //Pass back to Joomla
        if(!$response->isDownloadable() && $this->getDecorator() == 'joomla')
        {
            //Mimetype
            JFactory::getDocument()->setMimeEncoding($response->getContentType());

            //Set messages for any request method
            $messages = $response->getMessages();
            foreach($messages as $type => $group)
            {
                if ($type === 'success') {
                    $type = 'message';
                }

                foreach($group as $message) {
                    JFactory::getApplication()->enqueueMessage($message, $type);
                }
            }

            //Set the cache state
            JFactory::getApplication()->allowCache($context->getRequest()->isCacheable());

            //Do not flush the response
            return false;
        }
    }

    /**
     * Get the decorator name
     *
     * @return string
     */
    public function getDecorator()
    {
        return $this->getController()->getView()->getDecorator();
    }
}