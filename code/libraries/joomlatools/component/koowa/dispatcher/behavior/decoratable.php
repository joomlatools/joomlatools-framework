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
    protected function _initialize(KObjectConfig $config)
    {
        $config->append([
            'priority' => KDatabaseBehaviorAbstract::PRIORITY_LOW
        ]);
        parent::_initialize($config);
    }

    /**
     * Check if the behavior is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        $mixer   = $this->getMixer();
        $request = $mixer->getRequest();

        // Support HTML GET requests and also form submits (so we can render errors on POST)
        if(($request->isFormSubmit() || $request->isGet()) && $request->getFormat() == 'html' && !$request->isAjax()) {
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
        if ($this->getDecorator() != 'joomla')
        {
            $app = JFactory::getApplication();

            if ($app->isClient('site')) {
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
        $response = $context->getResponse();

        if(!$response->isDownloadable() && !$response->isRedirect())
        {
            //Render the page
            $result = $this->getObject('com:koowa.controller.page',  array('response' => $response))
                ->layout($this->getDecorator())
                ->render();


            //Set the result in the response
            $response->setContent($result);
        }
    }

    /**
     * Pass the response to Joomla
     *
     * @param   KDispatcherContextInterface $context A command context object
     * @return 	bool
     */
    protected function _beforeTerminate(KDispatcherContextInterface $context)
    {
        $response = $context->getResponse();

        //Pass back to Joomla
        if(!$response->isRedirect() && !$response->isDownloadable() && $this->getDecorator() == 'joomla')
        {
            //Contenttype
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
        $request  = $this->getRequest();
        $response = $this->getResponse();

        if($response->isError()) {
            $result = 'koowa';
        } else {
            $result = $this->getController()->getView()->getDecorator();
        }

        return $result;
    }
}