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
    }

    public function getRequest()
    {
        if(!$this->_request instanceof KDispatcherRequestInterface)
        {
            $request = parent::getRequest();

            $app = \JFactory::getApplication();
    
            if (!$this->isForwarded() && $app->isClient('site'))
            {
                $query = $request->getUrl()->getQuery(true);

                if (!empty($query['itemless']))
                {
                    foreach (['option', 'view'] as $parameter) {
                        if (!isset($query[$parameter])) $query[$parameter] = $request->getQuery()->$parameter;
                    } 

                    if (isset($query['Itemid'])) unset($query['Itemid']);

                    $request->setQuery($query); 
                }
            }

            $this->_request = $request;
        }

        return $this->_request;
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
