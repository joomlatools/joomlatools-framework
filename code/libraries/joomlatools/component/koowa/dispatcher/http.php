<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher
 */
class ComKoowaDispatcherHttp extends KDispatcherHttp
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config	An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.dispatch', '_enableExceptionHandler');

        //Render an exception before sending the response
        $this->addCommandCallback('before.fail', '_renderError');
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'behaviors'         => array('decoratable'),
            'response'          => 'com:koowa.dispatcher.response',
            'request'           => 'com:koowa.dispatcher.request',
            'event_subscribers' => array('unauthorized'),
            'user'              => 'com:koowa.user',
            'limit'             => array(
                'default' => JFactory::getConfig()->get('list_limit'),
                'max'     => 100
            ),
        ));

        parent::_initialize($config);
    }

    /**
     * Enables our own exception handler at all times before dispatching
     *
     * @param KDispatcherContextInterface $context
     */
    protected function _enableExceptionHandler(KDispatcherContextInterface $context)
    {
        $handler = $this->getObject('exception.handler');

        if (!$handler->isEnabled(KExceptionHandlerInterface::TYPE_EXCEPTION))
        {
            $handler->enable(KExceptionHandlerInterface::TYPE_EXCEPTION);
            $this->addCommandCallback('after.send', '_revertExceptionHandler');
        }
    }

    /**
     * Reverts exception handler to its previous status if it was enabled in {@link _enableExceptionHandler()}
     *
     * @param KDispatcherContextInterface $context
     */
    protected function _revertExceptionHandler(KDispatcherContextInterface $context)
    {
        $this->getObject('exception.handler')->disable(KExceptionHandlerInterface::TYPE_EXCEPTION);
    }

    /**
     * Render an exception
     *
     * @throws InvalidArgumentException If the action parameter is not an instance of Exception
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @return boolean|null
     */
    protected function _renderError(KDispatcherContextInterface $context)
    {
        $request   = $context->request;
        $response  = $context->response;

        //Check an exception was passed
        if(!isset($context->param) && !$context->param instanceof KException)
        {
            throw new InvalidArgumentException(
                "Action parameter 'exception' [KException] is required"
            );
        }

        //Get the exception object
        if($context->param instanceof KEventException) {
            $exception = $context->param->getException();
        } else {
            $exception = $context->param;
        }

        //Make sure the output buffers are cleared
        $level = ob_get_level();
        while($level > 0) {
            ob_end_clean();
            $level--;
        }

        //Render the error
        if(!JDEBUG && $request->getFormat() == 'html')
        {
            //If the error code does not correspond to a status message, use 500
            $code = $exception->getCode();
            if(!isset(KHttpResponse::$status_messages[$code])) {
                $code = '500';
            }

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
                $config = array(
                    'request'  => $request,
                    'response' => $response
                );

                $result = $this->getObject('com:koowa.controller.error',  $config)
                    ->layout('default')
                    ->render($exception);

                $response->setContent($result);
            }
        }
    }

    /**
     * Dispatch the request
     *
     * Dispatch to a controller internally. Functions makes an internal sub-request, based on the information in
     * the request and passing along the context.
     *
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @throws  KDispatcherExceptionMethodNotAllowed  If the method is not allowed on the resource.
     * @return  mixed
     */
    protected function _actionDispatch(KDispatcherContextInterface $context)
    {
        //Set the response messages
        $context->response->setMessages($this->getUser()->getSession()->getContainer('message')->all());

        return parent::_actionDispatch($context);
    }
}
