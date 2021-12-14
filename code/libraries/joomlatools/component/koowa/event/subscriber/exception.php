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
        //Make sure the output buffers are cleared
        $level = ob_get_level();
        while($level > 0) {
            ob_end_clean();
            $level--;
        }

        //Render debugger if Koowa or Joomla are running in debug mode, if not pass off to Joomla for handling
        if(Koowa::isDebug() || JDEBUG) {
            $this->_renderKoowaError($event);
        } else {
            $this->_renderJoomlaError($event);
        }
    }

    protected function _renderKoowaError(KEvent $event)
    {
        $request   = $this->getObject('request');
        $exception = $event->exception;

        //Render the exception backtrace if debug mode is enabled and format is html or json

        if(in_array($request->getFormat(), array('json', 'html')))
        {
            $dispatcher = $this->getObject('com:koowa.dispatcher.http');

            //Set status code (before rendering the error)
            $dispatcher->getResponse()->setStatus($this->_getErrorCode($exception));

            $content = $this->getObject('com:koowa.controller.error', ['request' => $request])
                            ->layout('default')
                            ->render($exception);

            //Set error in the response
            $dispatcher->getResponse()->setContent($content);
            $dispatcher->send();
        }
    }

    protected function _renderJoomlaError(KEvent $event)
    {
        $is_joomla4 = version_compare(JVERSION, 4, '>=');
        $request    = $this->getObject('request');

        // Only render the Error ourselves if we are running Joomla 3 and format is HTML
        if(!$is_joomla4 && class_exists('JErrorPage') && $request->getFormat() == 'html')
        {
            $exception = $event->exception;

            if(ini_get('display_errors')) {
                $message = $exception->getMessage();
            } else {
                $message = KHttpResponse::$status_messages[$this->_getErrorCode($exception)];
            }

            $message = $this->getObject('translator')->translate($message);
            $class = get_class($exception);
            $error = new $class($message, $exception->getCode());

            JErrorPage::render($error);
            JFactory::getApplication()->close(0);
        }
    }

    protected function _getErrorCode(\Throwable $exception)
    {
        //If the error code does not correspond to a status message, use 500

        $code = $exception->getCode();

        if(!isset(KHttpResponse::$status_messages[$code])) {
            $code = '500';
        }

        return $code;
    }
}