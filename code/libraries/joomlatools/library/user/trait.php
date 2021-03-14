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
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\User
 */
trait KUserTrait
{
    /**
     * User object or identifier
     *
     * @var	string|object
     */
    private $__user;

    /**
     * Set the user object
     *
     * @param mixed $user A user object or user object identifier
     * @return $this
     */
    public function setUser($user)
    {
        if($this->__user instanceof KUserInterface && $user instanceof KUserInterface) {
            $this->__user->setData($user->toArray());
        } else {
            $this->__user = $user;
        }

        return $this;
    }

    /**
     * Get the user object
     *
     * @param  string $identifier A unique user identifier, (i.e a username or email address)
     * @throws UnexpectedValueException	If the user doesn't implement the KUserInterface
     * @return KUserInterface
     */
    public function getUser($user = null)
    {
        if(is_null($user))
        {
            if(!$this->__user instanceof KUserInterface)
            {
                $this->__user = $this->getObject($this->__user ?? 'lib:user');

                if(!$this->__user instanceof KUserInterface)
                {
                    throw new UnexpectedValueException(
                        'User: '.get_class($this->__user).' does not implement KUserInterface'
                    );
                }
            }

            $result = $this->__user;
        }
        else $result = $this->getObject('user.provider')->load($user);

        return $result;
    }
}