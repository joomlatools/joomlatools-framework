<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Abstract Dispatcher
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher
 */
class KDispatcherHttp extends KDispatcherAbstract implements KObjectInstantiable, KObjectMultiton
{
    /**
     * List of methods supported by the dispatcher
     *
     * @var array
     */
    protected $_methods = array();

    /**
     * Constructor.
     *
     * @param KObjectConfig $config	An optional ObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Set the supported methods
        $this->_methods = KObjectConfig::unbox($config->methods);

        //Load the dispatcher translations
        $this->addCommandCallback('before.dispatch', '_loadTranslations');
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	KObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return 	void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'methods'        => array('get', 'head', 'post', 'put', 'patch', 'delete', 'options'),
            'behaviors'      => array('resettable'),
            'authenticators' => array('token', 'origin'),
            'limit'          => array('default' => 100)
         ));

        parent::_initialize($config);
    }

    /**
     * Force creation of a singleton
     *
     * @param  KObjectConfigInterface  $config  Configuration options
     * @param  KObjectManagerInterface $manager A KObjectManagerInterface object
     * @return KDispatcherDefault
     */
    public static function getInstance(KObjectConfigInterface $config, KObjectManagerInterface $manager)
    {
        //Merge alias configuration into the identifier
        $config->append($manager->getIdentifier('dispatcher')->getConfig());

        //Create the singleton
        $class    = $manager->getClass($config->object_identifier);
        $instance = new $class($config);

        //Add the object alias to allow easy access to the singleton
        $manager->registerAlias($config->object_identifier, 'dispatcher');

        return $instance;
    }

    /**
     * Load the controller translations
     *
     * @param KControllerContextInterface $context
     * @return void
     */
    protected function _loadTranslations(KControllerContextInterface $context)
    {
        $package = $this->getIdentifier()->package;
        $domain  = $this->getIdentifier()->domain;

        if($domain) {
            $identifier = 'com://'.$domain.'/'.$package;
        } else {
            $identifier = 'com:'.$package;
        }

        $this->getObject('translator')->load($identifier);
    }

    protected function _beforeDispatch(KDispatcherContextInterface $context)
    {
        $view = $context->request->query->get('view', 'cmd');

        //Redirect if no view information can be found in the request
        if(empty($view))
        {
            $url = clone($context->request->getUrl());
            $url->query['view'] = $this->getController()->getView()->getName();

            $this->redirect($url);
        }
        else $this->setController($view, array('view' => $view));
    }

    /**
     * Dispatch the request
     *
     * Dispatch to a controller internally. Functions makes an internal sub-request, based on the information in
     * the request and passing along the context.
     *
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @throws  KDispatcherExceptionMethodNotAllowed  If the method is not allowed on the resource.
     * @return	mixed
     */
	protected function _actionDispatch(KDispatcherContextInterface $context)
	{
        $view = $context->request->query->get('view', 'cmd');

        $method = strtolower($context->request->getMethod());

        if (!in_array($method, $this->getHttpMethods())) {
            throw new KDispatcherExceptionMethodNotAllowed('Method not allowed');
        }

        //Set the controller based on the view and pass the view
        $this->setController($view, array('view' => $view));

        //Execute the component method
        $this->execute($method, $context);

        return parent::_actionDispatch($context);
	}

    /**
     * Redirect
     *
     * Redirect to a URL externally. If no redirect status code has been specified in the response a 301 (permanent)
     * redirect will performed.
     *
     * @param KDispatcherContextInterface $context A dispatcher context object
     * @return bool
     */
    protected function _actionRedirect(KDispatcherContextInterface $context)
    {
        //Only set the status if it hasn't been set yet
        if(!$context->response->isRedirect()) {
            $context->response->setStatus(KDispatcherResponse::MOVED_PERMANENTLY);
        }

        $context->response->setRedirect($context->param);
        $this->send();

        return false;
    }

    /**
     * Get method
     *
     * This function translates a GET request into a render action.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-24
     *
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @return KModelEntityInterface
     */
    protected function _actionGet(KDispatcherContextInterface $context)
    {
        $controller = $this->getController();

        if($controller instanceof KControllerModellable)
        {
            $controller->getModel()->getState()->setProperty('limit', 'default', $this->getConfig()->limit->default);

            $limit = $this->getRequest()->query->get('limit', 'int');

            // Set to default if there is no limit. This is done for both unique and non-unique states
            // so that limit can be transparently used on unique state requests rendering lists.
            if(empty($limit)) {
                $limit = $this->getConfig()->limit->default;
            }

            if($this->getConfig()->limit->max && $limit > $this->getConfig()->limit->max) {
                $limit = $this->getConfig()->limit->max;
            }

            $this->getRequest()->query->limit = $limit;
            $controller->getModel()->getState()->limit = $limit;
        }

        return $controller->execute('render', $context);
    }

    /**
     * Head method
     *
     * @link https://tools.ietf.org/html/rfc7231#page-25
     *
     * @param KDispatcherContextInterface $context  A dispatcher context object
     * @return KModelEntityInterface
     */
    protected function _actionHead(KDispatcherContextInterface $context)
    {
        return $this->execute('get', $context);
    }

    /**
     * Post method
     *
     * This function translated a POST request action into an edit or add action. If the model state is unique a edit
     * action will be executed, if not unique an add action will be executed.
     *
     * If an _action parameter exists in the request data it will be used instead. If no action can be found an bad
     * request exception will be thrown.
     *
     * @linkv https://tools.ietf.org/html/rfc7231#page-25
     *
     * @param   KDispatcherContextInterface $context  A dispatcher context object
     * @throws  KDispatcherExceptionMethodNotAllowed  The action specified in the request is not allowed for the
     *          entity identified by the Request-URI. The response MUST include an Allow header containing a list of
     *          valid actions for the requested entity.
     * @throws  KControllerExceptionRequestInvalid    The action could not be found based on the info in the request.
     * @return  KModelEntityInterface
     */
    protected function _actionPost(KDispatcherContextInterface $context)
    {
        $result     = false;
        $action     = null;
        $controller = $this->getController();

        if($controller instanceof KControllerModellable)
        {
            //Get the action from the request data
            if($context->request->data->has('_action'))
            {
                $action = strtolower($context->request->data->get('_action', 'alnum'));

                if(in_array($action, array('browse', 'read', 'render', 'delete'))) {
                    throw new KDispatcherExceptionMethodNotAllowed('Action: '.$action.' not allowed');
                }
            }
            else
            {
                //Determine the action based on the model state
                $action = $controller->getModel()->getState()->isUnique() ? 'edit' : 'add';
            }

            //Throw exception if no action could be determined from the request
            if(!$action) {
                throw new KControllerExceptionRequestInvalid('Action not found');
            }

            //Execute the controller action
            $result = $controller->execute($action, $context);

            //Return the new representation of the resource
            if ($context->response->isSuccess())
            {
                if(!is_string($result) && !(is_object($result) && method_exists($result, '__toString'))) {
                    $result = $controller->execute('render', $context);
                }
            }
        }
        else throw new KDispatcherExceptionMethodNotAllowed('Method POST not allowed');

        return $result;
    }

    /**
     * Put method
     *
     * This function translates a PUT request into an edit or add action. Only if the model state is unique and the item
     * exists an edit action will be executed, if the entity does not exist and the state is unique an add action will
     * be executed.
     *
     * If the entity already exists it will be completely replaced based on the data available in the request. The request
     * needs to contain a complete entity representation.
     *
     * @linkv https://tools.ietf.org/html/rfc7231#page-26
     *
     * @param   KDispatcherContextInterface $context    A dispatcher context object
     * @throws  KControllerExceptionRequestInvalid  If the model state is not unique, or if the identity key is used.
     * @return  KModelEntityInterface
     */
    protected function _actionPut(KDispatcherContextInterface $context)
    {
        $action     = null;
        $controller = $this->getController();

        if($controller instanceof KControllerModellable)
        {
            $model = $controller->getModel();

            if($model->getState()->isUnique())
            {
                $entity = $model->fetch();

                if($entity->isNew())
                {
                    $action = 'add';

                    $identity_key = $entity->getIdentityKey();
                    if($identity_key && $model->getState()->get($identity_key)) {
                        throw new KControllerExceptionResourceNotFound('Resource not found');
                    }
                }
                else $action = 'edit';
            }
            else throw new KControllerExceptionRequestInvalid('Resource not found');

            //Execute the controller action
            $result = $controller->execute($action, $context);

            //Return the new representation of the resource
            if ($context->response->isSuccess())
            {
                if(!is_string($result) && !(is_object($result) && method_exists($result, '__toString'))) {
                    $result = $controller->execute('render', $context);
                }
            }
        }
        else throw new KDispatcherExceptionMethodNotAllowed('Method PUT not allowed');

        return $result;
    }

    /**
     * Patch method
     *
     * This function translates a PATCH request into an edit action.
     *
     * @link https://tools.ietf.org/html/rfc5789
     *
     * @param   KDispatcherContextInterface $context    A dispatcher context object
     * @throws  KControllerExceptionRequestInvalid  If the model state is not unique, or if the identity key is used.
     * @return  KModelEntityInterface
     */
    protected function _actionPatch(KDispatcherContextInterface $context)
    {
        $action     = null;
        $controller = $this->getController();

        if($controller instanceof KControllerModellable)
        {
            //Execute the controller action
            $result = $controller->execute('edit', $context);

            //Return the new representation of the resource
            if ($context->response->isSuccess())
            {
                if(!is_string($result) && !(is_object($result) && method_exists($result, '__toString'))) {
                    $result = $controller->execute('render', $context);
                }
            }
        }
        else throw new KDispatcherExceptionMethodNotAllowed('Method PATCH not allowed');

        return $result;
    }

    /**
     * Delete method
     *
     * This function translates a DELETE request into a delete action.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-29
     *
     * @param   KDispatcherContextInterface $context A dispatcher context object
     * @throws  KDispatcherExceptionMethodNotAllowed
     * @return  KModelEntityInterface
     */
    protected function _actionDelete(KDispatcherContextInterface $context)
    {
        $result     = false;
        $controller = $this->getController();

        if($controller instanceof KControllerModellable) {
            $result = $controller->execute('delete', $context);
        } else {
            throw new KDispatcherExceptionMethodNotAllowed('Method DELETE not allowed');
        }

        return $result;
    }

    /**
     * Options method
     *
     * Adds Allow header. Example output: `Allow: GET, POST, PUT, DELETE`
     *
     * @link https://tools.ietf.org/html/rfc7231#page-31
     *
     * @param   KDispatcherContextInterface $context    A dispatcher context object
     */
    protected function _actionOptions(KDispatcherContextInterface $context)
    {
        $agent   = $context->request->getAgent();
        $pattern = '#(?:Microsoft Office (?:Protocol|Core|Existence)|Microsoft-WebDAV)#i';

        if (preg_match($pattern, $agent)) {
            throw new KDispatcherExceptionMethodNotAllowed('Method not allowed');
        }

        $methods = array();

        //Retrieve HTTP methods allowed by the dispatcher
        $actions = array_intersect($this->getActions(), $this->getHttpMethods());

        foreach($actions as $action)
        {
            if($this->canExecute($action)) {
                $methods[] = strtoupper($action);
            }
        }

        //See: https://tools.ietf.org/html/rfc7231#section-7.4.1
        $context->response->headers->set('Allow', implode(', ', $methods));

        //Set Accept-Post header to framework defaults
        if(array_search('post', $this->getHttpMethods())) {
            $context->response->headers->set('Accept-Post', ['application/x-www-form-urlencoded', 'application/json']);
        }

        //Set Accept-Put header to framework defaults
        if(array_search('put', $this->getHttpMethods())) {
            $context->response->headers->set('Accept-Put', ['application/x-www-form-urlencoded', 'application/json']);
        }

        //Set Accept-Patch header to framework defaults
        //See: https://tools.ietf.org/html/rfc5789#page-7
        if(array_search('patch', $this->getHttpMethods())) {
            $context->response->headers->set('Accept-Patch', ['application/x-www-form-urlencoded', 'application/json']);
        }
    }

    /**
     * Send the response to the client
     *
     * Add an Allow header to the response if the status code is 405 METHOD NOT ALLOWED.
     *
     * {@inheritdoc}
     */
    protected function _actionSend(KDispatcherContextInterface $context)
    {
        $request  = $this->getRequest();
        $response = $this->getResponse();

        //Add an Allow header to the response
        if($response->getStatusCode() === KHttpResponse::METHOD_NOT_ALLOWED)
        {
            try {
                $this->_actionOptions($context);
            }
            catch (Exception $e) {}
        }

        parent::_actionSend($context);
    }

    /**
     * Get the supported methods
     *
     * @return array
     */
    public function getHttpMethods()
    {
        return $this->_methods;
    }
}