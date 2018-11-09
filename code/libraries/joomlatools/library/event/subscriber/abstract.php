<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event Subscriber
 *
 * An EventSusbcriber knows himself what events he is interested in. Classes extending the abstract implementation may
 * be adding listeners to an EventDispatcher through the {@link subscribe()} method.
 *
 * Listeners must be public class methods following a camel Case naming convention starting with 'on', eg onFooBar. The
 * listener priority is usually between 1 (high priority) and 5 (lowest), default is 3 (normal)
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Event\Subscriber
 */
abstract class KEventSubscriberAbstract extends KObject implements KEventSubscriberInterface, KObjectMultiton
{
    /**
     * List of subscribed publishers
     *
     * @var array
     */
    private $__publishers;

    /**
     * Attach one or more listeners
     *
     * Event listeners always start with 'on' and need to be public methods.
     *
     * @param KEventPublisherInterface $publisher
     * @param  integer                 $priority   The event priority, usually between 1 (high priority) and 5 (lowest),
     *                                 default is 3 (normal)
     * @return array An array of public methods that have been attached
     */
    public function subscribe(KEventPublisherInterface $publisher, $priority = KEvent::PRIORITY_NORMAL)
    {
        $handle    = $publisher->getHandle();
        $listeners = [];

        if(!$this->isSubscribed($publisher))
        {
            $listeners = $this->getEventListeners();

            foreach ($listeners as $listener)
            {
                $publisher->addListener($listener, array($this, $listener), $priority);
                $this->__publishers[$handle][] = $listener;
            }
        }

        return $listeners;
    }

    /**
     * Detach all previously attached listeners for the specific dispatcher
     *
     * @param KEventPublisherInterface $publisher
     * @return void
     */
    public function unsubscribe(KEventPublisherInterface $publisher)
    {
        $handle = $publisher->getHandle();

        if($this->isSubscribed($publisher))
        {
            foreach ($this->__publishers[$handle] as $index => $listener)
            {
                $publisher->removeListener($listener, array($this, $listener));
                unset($this->__publishers[$handle][$index]);
            }
        }
    }

    /**
     * Check if the subscriber is already subscribed to the dispatcher
     *
     * @param  KEventPublisherInterface $publisher  The event dispatcher
     * @return boolean TRUE if the subscriber is already subscribed to the dispatcher. FALSE otherwise.
     */
    public function isSubscribed(KEventPublisherInterface $publisher)
    {
        $handle = $publisher->getHandle();
        return isset($this->__publishers[$handle]);
    }

    /**
     * Get the event listeners
     *
     * @return array
     */
    public static function getEventListeners()
    {
        $listeners = array();

        $reflection = new ReflectionClass(get_called_class());
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if(substr($method->name, 0, 2) == 'on') {
               $listeners[] = $method->name;
            }
        }

        return $listeners;
    }
}
