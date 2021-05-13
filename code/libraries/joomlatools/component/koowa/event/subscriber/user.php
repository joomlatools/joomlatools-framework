<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * User Event Subscriber
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaEventSubscriberUser extends KEventSubscriberAbstract
{
    /**
     * Makes sure both Koowa and Joomla users are in sync after user login
     */
    public function onAfterUserLogin(KEventInterface $event)
    {
        $user = $this->getObject('user');

        if (!$user->isAuthentic()) {
            $user->setData($event->user);
        }

        $menu = $this->getObject('joomla')->app->getMenu();

        // Hack for syncing the authenticated user object on the Joomla menu instance
        $set_user = Closure::bind(function($user)
        {
            if (!$this->user->id) {
                $this->user = $user;
            }
        }, $menu, $menu);

        $set_user($event->user);
    }
}