<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * User Trait
 *
 * This trait implements specific Joomla user functionality
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\User
 */
trait ComKoowaUserTrait
{
    private $__groups  = null;
    private $__roles   = null;

    /**
     * Returns the username of the user
     *
     * @return string The name
     */
    public function getUsername()
    {
        return $this->getData()->username;
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
        $roles = parent::getRoles();

        //Convert to names
        if($by_name)
        {
            if(!isset($this->__roles))
            {
                //Get the user roles
                $roles = $this->getObject('com:koowa.database.table.roles')
                    ->select($roles, KDatabase::FETCH_ARRAY_LIST);

                $this->__roles = array_map('strtolower', array_column($roles, 'title'));
            }

            $roles = $this->__roles;
        }

        return $roles;
    }

    /**
     * Checks if the user has a role.
     *
     * @param  mixed|array $role A role name or an array containing role names.
     * @param  bool        $strict If true, the user has to have all the provided roles, not just one
     * @return bool
     */
    public function hasRole($roles, $strict = false)
    {
        $result = false;

        foreach((array)$roles as $role)
        {
            if(is_numeric($role)) {
                $result = in_array($role, $this->getRoles());
            } else {
                $result = in_array(strtolower($role), $this->getRoles(true));
            }

            if(!$strict) {
                if($result == true) break;
            } else {
                if($result == false) break;
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
        $groups = parent::getGroups();

        //Convert to names
        if($by_name)
        {
            if(!isset($this->__groups))
            {
                //Get the user groups
                $groups = $this->getObject('com:koowa.database.table.groups')
                    ->select($groups, KDatabase::FETCH_ARRAY_LIST);

                $this->__groups = array_map('strtolower', array_column($groups, 'title'));
            }

            $groups = $this->__groups;
        }

        return $groups;
    }

    /**
     * Checks if the user is part of a group
     *
     * @param  mixed|array $group A role name or an array containing group names.
     * @param  bool        $strict If true, the user needs to be part of all provided group(s), not just one.
     * @return bool
     */
    public function hasGroup($groups, $strict = false)
    {
        $result = false;

        foreach((array) $groups as $group)
        {
            if(is_numeric($group)) {
                $result = in_array($group, $this->getGroups());
            } else {
                $result = in_array(strtolower($group), $this->getGroups(true));
            }

            if(!$strict) {
                if($result == true) break;
            } else {
                if($result == false) break;
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