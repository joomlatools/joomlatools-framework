<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Exception Event Subscriber
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaEventSubscriberException extends KEventSubscriberAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => KEvent::PRIORITY_LOW
        ));

        parent::_initialize($config);
    }

    /**
     * Render an exception
     *
     * @throws InvalidArgumentException If the action parameter is not an instance of Exception
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @return boolean|null
     */
    public function onException(KEvent $event)
    {
        $request   = $this->getObject('request');
        $exception = $event->exception;

        //Make sure the output buffers are cleared
        $level = ob_get_level();
        while($level > 0) {
            ob_end_clean();
            $level--;
        }

        //If the error code does not correspond to a status message, use 500
        $code = $exception->getCode();
        if(!isset(KHttpResponse::$status_messages[$code])) {
            $code = '500';
        }

        //Render the error
        if(class_exists('JErrorPage' && !JDEBUG && $request->getFormat() == 'html'))
        {
            if(ini_get('display_errors')) {
                $message = $exception->getMessage();
            } else {
                $message = KHttpResponse::$status_messages[$code];
            }

            $message = $this->getObject('translator')->translate($message);

            $class = get_class($exception);
            $error = new $class($message, $exception->getCode());
            JErrorPage::render($error);

            JFactory::getApplication()->close(0);

            return false;
        }
        else
        {
            //Render the exception if debug mode is enabled or if we are returning json
            if(in_array($request->getFormat(), array('json', 'html')))
            {
                $dispatcher = $this->getObject('com:koowa.dispatcher.http');

                //Set status code (before rendering the error)
                $dispatcher->getResponse()->setStatus($code);

                $content = $this->getObject('com:koowa.controller.error',  ['request'  => $request])
                    ->layout('default')
                    ->render($exception);

                //Set error in the response
                $dispatcher->getResponse()->setContent($content);
                $dispatcher->send();
            }
        }
    }
}