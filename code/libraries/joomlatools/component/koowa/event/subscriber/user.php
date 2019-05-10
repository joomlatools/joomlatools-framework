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
            $user->setUser($event->user);
        }

        // Hack for syncing the authenticated user object on the Joomla menu instance
        ComKoowaJMenu::setUser($event->user);
    }
}

/**
 * Koowa Joomla Menu
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaJMenu extends JMenu
{
    public static function setUser($user)
    {
        $client = JFactory::getApplication()->getName();

        $menu = self::getInstance($client);

        if (!$menu->user->id) {
            $menu->user = $user;
        }
    }
}