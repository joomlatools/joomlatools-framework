<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Origin Dispatcher Authenticator
 *
 * This authenticator implements origina and referrer based csrf mitigation
 *
 * @link https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.md#verifying-origin-with-standard-headers
 * @link https://seclab.stanford.edu/websec/csrf/csrf.pdf
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Authenticator
 */
class KDispatcherAuthenticatorOrigin extends KDispatcherAuthenticatorAbstract
{
    /**
     * Constructor
     *
     * @param KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.put'   , 'authenticateRequest');
        $this->addCommandCallback('before.post'  , 'authenticateRequest');
        $this->addCommandCallback('before.patch' , 'authenticateRequest');
        $this->addCommandCallback('before.delete', 'authenticateRequest');
    }

    /**
     * Verify the request to prevent CSRF exploit
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
        $origin  = false;
        $request = $context->request;

        //No Origin, fallback to Referer
        if(!$origin = $request->headers->get('Origin')) {
            $origin = $request->headers->get('Referer');
        }

        //Don't not allow origin to be empty or null (possible in some cases)
        if(!empty($origin))
        {
            $origin = $this->getObject('lib:filter.url')->sanitize($origin);

            $target = $request->getUrl()->getHost();
            $source = KHttpUrl::fromString($origin)->getHost();

            // Check if the source matches the target
            if($target !== $source)
            {
                // Special case - check if the source is a subdomain of the target origin
                if ('.'.$target !== substr($source, -1 * (strlen($target)+1))) {
                    throw new KControllerExceptionRequestInvalid('Origin or referer not valid');
                }
            }
        }
        else throw new KControllerExceptionRequestInvalid('Origin or referer required');


        return true;
    }
}