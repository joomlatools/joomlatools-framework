<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Token Dispatcher Authenticator
 *
 * This authenticator implements token based csrf mitigation using the synchroniser token pattern. The csrf token is
 * only checked if a session is active.
 *
 * @link https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.md#token-based-mitigation
 * @link https://seclab.stanford.edu/websec/csrf/csrf.pdf
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Authenticator
 */
class KDispatcherAuthenticatorToken extends KDispatcherAuthenticatorAbstract
{
    /**
     * The CSRF token
     *
     * @var string
     */
    private $__token;

    /**
     * Constructor
     *
     * @param KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.dispatch', 'authenticateRequest');
        $this->addCommandCallback('after.get', 'signResponse');
    }

    /**
     * Return the CSRF request token
     *
     * @return  string  The CSRF token or NULL if no token could be found
     */
    public function getCsrfToken()
    {
        if(!isset($this->__token))
        {
            $token   = false;
            $request = $this->getObject('request');

            if($request->headers->has('X-XSRF-Token')) {
                $token = $request->headers->get('X-XSRF-Token');
            }

            if($request->headers->has('X-CSRF-Token')) {
                $token = $request->headers->get('X-CSRF-Token');
            }

            if($request->data->has('csrf_token')) {
                $token = $request->data->get('csrf_token', 'sha1');
            }

            $this->__token = $token;
        }

        return $this->__token;
    }

    /**
     * Verify the request to prevent CSRF exploits
     *
     * @param KDispatcherContextInterface $context	A dispatcher context object
     *
     * @throws KControllerExceptionRequestInvalid      If the request referrer is not valid
     * @throws KControllerExceptionRequestForbidden    If the cookie token is not valid
     * @throws KControllerExceptionRequestNotAuthenticated If the session token is not valid
     * @return  boolean Returns FALSE if the check failed. Otherwise TRUE.
     */
    public function authenticateRequest(KDispatcherContextInterface $context)
    {
        //Check the raw request method to bypass method overrides
        if($context->user->isAuthentic() && !$context->isAuthentic()  && $this->isPost())
        {
            //Check csrf token
            if(!$this->getCsrfToken()) {
                throw new KControllerExceptionRequestNotAuthenticated('Csrf Token Not Found');
            }

            //Check session token
            if( $this->getCsrfToken() !== $context->user->getSession()->getToken()) {
                throw new KControllerExceptionRequestForbidden('Invalid Session Token');
            }

            // Explicitly authenticate the request
            $context->setAuthentic();
        }

        return true;
    }

    /**
     * Sign the response with a session token
     *
     * @param KDispatcherContextInterface $context	A dispatcher context object
     */
    public function signResponse(KDispatcherContextInterface $context)
    {
        if(!$context->response->isError() && $context->isAuthentic())
        {
            $token = $context->user->getSession()->getToken();
            $context->response->headers->set('X-CSRF-Token', $token);
        }
    }

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST';
    }
}