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
     * A map of wrapped event handlers
     *
     * @var array
     */
    private $__event_handlers = [];

    /**
     * Converts a callable into a string for hashing
     *
     * @param string|closure $handler
     * @return string
     */
    private function __getEventHandlerHash($handler)
    {
        return $handler instanceof Closure ? spl_object_hash($handler) : serialize($handler);
    }

    /**
     * Attach a Joomla event handler
     *
     * In Joomla 4 it wraps the event handler into a lambda function to unpack arguments from the event object
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

        if (class_exists('JEventDispatcher')) {
            JEventDispatcher::getInstance()->attach(['event' => $event, 'handler' => $handler]);
        } else {
            $this->__event_handlers[$this->__getEventHandlerHash($handler)] = function($event) use($handler) {
                // Get the event arguments
                $arguments = $event->getArguments();

                // Extract any old results; they must not be part of the method call.
                $allResults = [];

                if (isset($arguments['result']))
                {
                    $allResults = $arguments['result'];

                    unset($arguments['result']);
                }

                // Convert to indexed array for unpacking.
                $arguments = \array_values($arguments);

                $result = $handler(...$arguments);

                // Ignore null results
                if ($result === null)
                {
                    return;
                }

                // Restore the old results and add the new result from our method call
                $allResults[]    = $result;
                $event['result'] = $allResults;
            };

            JFactory::getApplication()->getDispatcher()->addListener($event, $this->__event_handlers[$this->__getEventHandlerHash($handler)]);
        }


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

        if (class_exists('JEventDispatcher')) {
            JEventDispatcher::getInstance()->detach(['event' => $event, 'handler' => $handler]);
        } else {
            JFactory::getApplication()->getDispatcher()->removeListener($event, $this->__event_handlers[$this->__getEventHandlerHash($handler)]);
        }

        return $this;
    }
}
