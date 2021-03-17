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
 * @package Koowa\Library\Event\Subscriber
 */
class KEventSubscriberException extends KEventSubscriberAbstract
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
    public function onException(KEventException $event)
    {
        $request   = $this->getObject('request');
        $response  = $this->getObject('response');

        $exception = $event->getException();

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

        //Get the error message
        $message = KHttpResponse::$status_messages[$code];

        //Set the response status
        $response->setStatus($code , $message);

        //Send the response
        $response->send();
    }
}