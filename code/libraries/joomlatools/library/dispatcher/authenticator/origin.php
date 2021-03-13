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
 * This authenticator implements origin and referrer based csrf mitigation
 *
 * @link https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.md#verifying-origin-with-standard-headers
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

        $this->addCommandCallback('before.dispatch', 'authenticateRequest');
    }

    /**
     * Verify the request to prevent CSRF exploits
     *
     * We first check if X-Requested-With header is present or not. If it is, the request is coming from an identified
     * origin as it's a non-safe CORS header and a form submit from a third party website wouldn't include the header.
     * If the browser cleared the request to hit our end after passing the CORS preflight request we deem the request
     * safe.
     *
     * See: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#use-of-custom-request-headers
     *
     * If the header is not present (any other POST request like a normal form submit we check for `Origin` header with
     * a fallback to `Referer` header. If the origin (or referer) header is present and on our list of allowed origins
     * we deem the request safe.
     *
     * See: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#verifying-origin-with-standard-headers
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
        if(!$context->isAuthentic() && $this->isPost())
        {
            $request = $context->request;

            // Mere presence of the X-Requested-With header is a sign that the request is coming from an identified origin:
            if ($request->headers->has('X-Requested-With'))
            {
                // Explicitly authenticate the request
                $context->setAuthentic();
            }
            else
            {
                $origin  = $request->headers->get('Origin');

                //No Origin, fallback to Referer
                if(!$origin) {
                    $origin = $request->headers->get('Referer');
                }

                //Don't not allow origin to be empty or null (possible in some cases)
                if(!empty($origin))
                {
                    $match  = false;
                    $origin = $this->getObject('lib:filter.url')->sanitize($origin);
                    $source = KHttpUrl::fromString($origin)->getHost();

                    foreach($request->getOrigins() as $target)
                    {
                        // Check if the source matches the target
                        if($target == $source || '.'.$target === substr($source, -1 * (strlen($target)+1))) {
                            $match = true; break;
                        }
                    }

                    if(!$match) {
                        throw new KControllerExceptionRequestInvalid('Origin or referer not valid');
                    }

                    // Explicitly authenticate the request
                    $context->setAuthentic();
                }
                else throw new KControllerExceptionRequestInvalid('Origin or referer required');
            }
        }

        return true;
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