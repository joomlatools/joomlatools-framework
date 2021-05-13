<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Jwt Dispatcher Authenticator
 *
 * A token MAY contain and additional 'user' claim which contains a JSON hash of user field key and values to set on
 * the user.
 *
 * Supported fields :
 *
 * - fullname
 * - email
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Authenticator
 */
class ComKoowaDispatcherAuthenticatorJwt extends KDispatcherAuthenticatorJwt
{
    /**
     * Options used when logging in the user
     *
     * @var boolean
     */
    protected $_options;

    /**
     * Constructor.
     *
     * @param   KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_options = KObjectConfig::unbox($config->options);
    }

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
            'secret'  => $this->getObject('joomla')->config->get('secret'),
            'max_age' => $this->getObject('joomla')->config->get('lifetime') * 60,
            'options' => array(
                'action'       => $this->getObject('joomla')->isSite() ? 'core.login.site' : 'core.login.admin',
                'autoregister' => false,
                'type'         => 'jwt'
            ),
        ));

        parent::_initialize($config);
    }

    /**
     * Log the user in
     *
     * @param string $username
     * @param array  $data
     * @return boolean
     */
    protected function _loginUser($username, $data = array())
    {
        $data['username'] = $username;

        $parameter        = $this->getObject('joomla')->isAdmin() ? 'admin_language' : 'language';
        $data['language'] = $this->getUser($username)->get($parameter);

        $options = $this->_options;

        $this->getObject('joomla')->pluginHelper->importPlugin('user');
        $results = $this->getObject('joomla')->app->triggerEvent('onUserLogin', array($data, $options));

        // The user is successfully logged in. Refresh the current user.
        if (in_array(false, $results, true) == false)
        {
            parent::_loginUser($username);

            // Publish the onUserAfterLogin event to make sure that user instances are synced (see: ComKoowaEventSubscriberUser::onAfterUserLogin)
            $this->getObject('event.publisher')
                ->publishEvent('onAfterUserLogin', array('user' => $this->getObject('joomla')->user($username)), $this->getObject('joomla')->app);


            return true;
        }

        return false;
    }
}