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
    use ComKoowaUserTrait;

    /**
     * Constructor
     *
     * @param KObjectConfig $config An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        KObject::__construct($config);

        //Set the user properties and attributes
        $this->setData(JFactory::getUser());
    }

    /**
     * Set the user data
     *
     * @param  array|JUser|Joomla\CMS\User\User $user An associative array of data or a JUser object
     * @return $this
     */
    public function setData($user)
    {
        if($user instanceof JUser || $user instanceof \Joomla\CMS\User\User)
        {
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
                'expired'    => !$user->activation,
                'attributes' => $user->getParameters()->toArray()
            );
        }
        else $data = $user;

        return parent::setData($data);
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
}