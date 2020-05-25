<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * User
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\User
 */
final class ComKoowaUser extends KUser implements ComKoowaUserInterface
{
    private $__groups  = null;
    private $__roles   = null;

    protected function _initialize(KObjectConfig $config)
    {
        $user = JFactory::getUser();

        $config->append(array(
            'data' => $this->_mapData($user)
        ));

        parent::_initialize($config);
    }

    /**
     * User setter
     *
     * @param JUser $user A joomla user object
     *
     * @return $this
     */
    public function setUser(JUser $user)
    {
        $this->setData($this->_mapData($user));

        return $this;
    }

    /**
     * Joomla user to Koowa user data mapper
     *
     * @param JUser $user
     *
     * @return array Koowa user data
     */
    protected function _mapData(JUser $user)
    {
        return array(
            'id'         => $user->id,
            'email'      => $user->email,
            'name'       => $user->name,
            'username'   => $user->username,
            'password'   => $user->password,
            'salt'       => '',
            'authentic'  => !$user->guest,
            'enabled'    => !$user->block,
            'expired'    => !$user->activation,
            'attributes' => $user->getParameters()->toArray()
        );
    }

    /**
     * Returns the username of the user
     *
     * @return string The name
     */
    public function getUsername()
    {
        return $this->getSession()->get('user.username');
    }

    /**
     * Method to get a parameter value
     *
     * @param   string  $key      Parameter key
     * @param   mixed   $default  Parameter default value
     * @return  mixed  The value or the default if it did not exist
     */
    public function getParameter($key, $default = null)
    {
        return JFactory::getUser()->getParam($key, $default);
    }

    /**
     * Returns the roles of the user
     *
     * @param  bool  $by_name Return the roles by name instead of by id
     * @return array The role id's or names
     */
    public function getRoles($by_name = false)
    {
        $data  = $this->getData();
        $roles = KObjectConfig::unbox($data->roles);

        if(empty($roles)) {
            $this->getSession()->set('user.roles', JAccess::getAuthorisedViewLevels($this->getId()));
        }

        //Convert to names
        if($by_name)
        {
            if(!isset($this->__roles))
            {
                //Get the user roles
                $roles = $this->getObject('com:koowa.database.table.roles')
                    ->select(parent::getRoles(), KDatabase::FETCH_ARRAY_LIST);

                $this->__roles = array_map('strtolower', array_column($roles, 'title'));
            }

            $result = $this->__roles;
        }
        else $result = parent::getRoles();

        return $result;
    }

    /**
     * Checks if the user has a role.
     *
     * @param  mixed|array $roles A role name or id or an array containing role names id's.
     * @return bool True if the user has at least one of the provided roles, false otherwise.
     */
    public function hasRole($roles)
    {
        $result = false;

        foreach((array)$roles as $role)
        {
            if(is_numeric($role)) {
                $result = in_array($role, $this->getRoles());
            } else {
                $result = in_array($role, $this->getRoles(true));
            }

            if($result == true) {
                break;
            }
        }

        return (bool) $result;
    }

    /**
     * Returns the groups the user is part of
     *
     * @param  bool  $by_name Return the groups by name instead of by id
     * @return array An array of group id's or names
     */
    public function getGroups($by_name = false)
    {
        $data  = $this->getData();
        $groups = KObjectConfig::unbox($data->groups);

        if(empty($groups)) {
            $this->getSession()->set('user.groups', JAccess::getGroupsByUser($this->getId()));
        }

        //Convert to names
        if($by_name)
        {
            if(!isset($this->__groups))
            {
                //Get the user groups
                $groups = $this->getObject('com:koowa.database.table.groups')
                    ->select(parent::getGroups(), KDatabase::FETCH_ARRAY_LIST);

                $this->__groups = array_map('strtolower', array_column($groups, 'title'));
            }

            $result = $this->__groups;
        }
        else $result =  parent::getGroups();

        return $result;
    }

    /**
     * Checks if the user is part of a group.
     *
     * @param  mixed|array $groups A group name or id or an array containing group names or id's.
     * @return bool True if the user has at least one of the provided groups, false otherwise.
     */
    public function hasGroup($groups)
    {
        $result = false;

        foreach((array) $groups as $group)
        {
            if(is_numeric($group)) {
                $result = in_array($group, $this->getGroups());
            } else {
                $result = in_array($group, $this->getGroups(true));
            }

            if($result == true) {
                break;
            }
        }

        return (bool) $result;
    }

    /**
     * Method to check object authorisation against an access control object and optionally an access extension object
     *
     * @param   string  $action     The name of the action to check for permission.
     * @param   string  $assetname  The name of the asset on which to perform the action.
     * @return  boolean  True if authorised
     */
    public function authorise($action, $assetname = null)
    {
        return JFactory::getUser()->authorise($action, $assetname);
    }
}