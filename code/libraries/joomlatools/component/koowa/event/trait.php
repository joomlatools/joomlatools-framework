<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event Handler Trait
 *
 * Trait to allow attaching and detaching Joomla event handlers
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event
 */
trait ComKoowaEventTrait
{
    /**
     * Attach a Joomla event handler
     *
     * @param string  $event The name of the event
     * @param string|closure $handler The event handler
     * @return $this
     */
    public function attachEventHandler($event, $handler)
    {
        if(!is_callable($handler)) {
            $handler = array($this, $handler);
        }

        JEventDispatcher::getInstance()->attach([
            'event' => $event,
            'handler' => $handler
        ]);

        return $this;
    }

    /**
     * Detatch a Joomla event handler
     *
     * @param string  $event The name of the event
     * @param string|closure$handler The event handler
     * @return $this
     */
    public function detachEventHandler($event, $handler)
    {
        if(!is_callable($handler)) {
            $handler = array($this, $handler);
        }

        JEventDispatcher::getInstance()->detach([
            'event' => $event,
            'handler' => $handler
        ]);

        return $this;
    }
}
