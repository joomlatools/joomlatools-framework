<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * User Provider
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\User
 */
final class ComKoowaUserProvider extends KUserProvider
{
    /**
     * Loads the user for the given username or user id
     *
     * @param string $identifier A unique user identifier, (i.e a username or user id)
     * @param bool  $refresh     If TRUE and the user has already been loaded it will be re-loaded.
     * @return ComKoowaUserInterface Returns a UserInterface object.
     */
    public function load($identifier, $refresh = false)
    {
        $user = $this->getObject('user');

        // Find the user id
        if (!is_numeric($identifier))
        {
            if(!$identifier = JUserHelper::getUserId($identifier)) {
                $identifier = 0;
            }
        }

        // Fetch the user
        if ($identifier == 0 || $user->getId() != $identifier)
        {
            $user = parent::load($identifier, $refresh);

            if (!$user instanceof ComKoowaUserInterface)
            {
                $user = $this->create(array(
                    'id'   => $identifier,
                    'name' => $this->getObject('translator')->translate('Anonymous')
                ));
            }
        }

        return $user;
    }

    /**
     * Fetch the user for the given user identifier from the backend
     *
     * @param string $identifier A unique user identifier, (i.e a username or email address)
     * @return ComKoowaUserInterface|null Returns a UserInterface object or NULL if the user could not be found.
     */
    public function fetch($identifier)
    {
        $table = JUser::getTable();

        if ($table->load($identifier))
        {
            $user = JUser::getInstance(0);
            $user->setProperties($table->getProperties());

            $params = new JRegistry;
            $params->loadString($table->params);

            $user->setParameters($params);

            // Get params from the protected property
            $getParams   = Closure::bind(function() {
                return $this->_params->toArray();
            }, $user, $user);

            $data = array(
                'id'         => $user->id,
                'email'      => $user->email,
                'name'       => $user->name,
                'username'   => $user->username,
                'password'   => $user->password,
                'salt'       => '',
                'groups'     => JAccess::getGroupsByUser($user->id),
                'roles'      => JAccess::getAuthorisedViewLevels($user->id),
                'authentic'  => !$user->guest,
                'enabled'    => !$user->block,
                'expired'    => (bool) $user->activation,
                'attributes' => $getParams()
            );

            $user = $this->create($data);
        }
        else $user = null;

        return $user;
    }

    /**
     * Create a user object
     *
     * @param array $data An associative array of user data
     * @return ComKoowaUserInterface     Returns a UserInterface object
     */
    public function create($data)
    {
        $user = $this->getObject('com:koowa.user.default', array('data' => $data));
        return $user;
    }

    /**
     * Store a user object in the provider
     *
     * @param string $identifier A unique user identifier, (i.e a username or email address)
     * @param array $data An associative array of user data
     * @return ComKoowaUserInterface     Returns a UserInterface object
     */
    public function store($identifier, $data)
    {
        // Find the user id
        if (!is_numeric($identifier))
        {
            if(!$identifier = JUserHelper::getUserId($identifier)) {
                $identifier = 0;
            }
        }

        return parent::store($identifier, $data);
    }

    /**
     * Check if a user has already been loaded for a given user identifier
     *
     * @param $identifier
     * @return boolean TRUE if a user has already been loaded. FALSE otherwise
     */
    public function isLoaded($identifier)
    {
        $user = $this->getObject('user');

        // Find the user id
        if (!is_numeric($identifier))
        {
            if(!$identifier = JUserHelper::getUserId($identifier)) {
                $identifier = 0;
            }
        }

        if($identifier == 0 || $user->getId() != $identifier) {
            $result = isset($this->_users[$identifier]);
        } else {
            $result = true;
        }

        return $result;
    }
}