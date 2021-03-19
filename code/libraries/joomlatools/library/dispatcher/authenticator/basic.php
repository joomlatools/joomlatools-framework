<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2017 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Form Dispatcher Authenticator
 *
 * If you are running PHP as CGI. Apache does not pass HTTP Basic user/pass to PHP by default.
 * To fix this add these lines to your .htaccess file:
 *
 * RewriteCond %{HTTP:Authorization} ^(.+)$
 * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Authenticator
 */
class KDispatcherAuthenticatorBasic extends KDispatcherAuthenticatorAbstract
{
    /**
     * The username, password tuple from the Authorization header
     *
     * @var array
     */
    private $__auth_param;

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_HIGH,
        ));

        parent::_initialize($config);
    }

    /**
     * Returns the username from the basic authentication credentials
     *
     * @return null|string
     */
    public function getUsername()
    {
        $result = null;

        if ($param = $this->_getAuthParam()) {
            $result = $param[0];
        }

        return $result;
    }

    /**
     * Returns the password from the basic authentication credentials
     *
     * @return null|string
     */
    public function getPassword()
    {
        $result = null;

        if ($param = $this->_getAuthParam()) {
            $result = $param[1];
        }

        return $result;
    }

    /**
     * Authenticate using email and password credentials
     *
     * @param KDispatcherContextInterface $context A dispatcher context object
     * @return  boolean Returns TRUE if the authentication explicitly succeeded.
     */
    public function authenticateRequest(KDispatcherContextInterface $context)
    {
        if(!$context->user->isAuthentic() && $username = $this->getUsername())
        {
            if($result = $this->_loginUser($username))
            {
                //Explicitly authenticate the request
                $context->setAuthentic();
            }

            return $result;
        }

        return false;
    }

    /**
     * Returns the basic authentication credentials from the header
     *
     * @return array|null
     */
    protected function _getAuthParam()
    {
        if(!isset($this->__auth_param))
        {
            $this->__auth_param = null;

            $request = $this->getObject('request');

            if($request->headers->has('Authorization'))
            {
                $authorization = $request->headers->get('Authorization');

                if (stripos($authorization, 'basic') === 0)
                {
                    $exploded = explode(':', base64_decode(substr($authorization, 6)));

                    if (count($exploded) == 2) {
                        $this->__auth_param = array($exploded[0], $exploded[1]);
                    }
                }
            }
        }

        return $this->__auth_param;
    }

    /**
     * Log the user in
     *
     * @param string $username  A user key or name
     * @param array  $data      Optional user data
     *
     * @return bool
     */
    protected function _loginUser($username, $data = array())
    {
        //Set user data in context
        $data = $this->getUser($username)->toArray();
        $data['authentic'] = true;

        $this->getUser()->setData($data);

        // Explicitly authenticate user
        $this->getUser()->setAuthentic();

        return true;
    }
}