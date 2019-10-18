<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event Subscriber Interface
 *
 * An EventSusbcriber knows himself what events he is interested in. Classes implementing this interface may be adding
 * listeners to an EventDispatcher through the {@link subscribe()} method.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Event\Subscriber
 */
interface KEventSubscriberInterface
{
    /**
     * Priority levels
     */
    const PRIORITY_HIGHEST = 1;
    const PRIORITY_HIGH    = 2;
    const PRIORITY_NORMAL  = 3;
    const PRIORITY_LOW     = 4;
    const PRIORITY_LOWEST  = 5;

    /**
     * Register one or more listeners
     *
     * @param KEventPublisherInterface $publisher
     * @@return array An array of public methods that have been attached
     */
    public function subscribe(KEventPublisherInterface $publisher);

    /**
     * Unsubscribe all previously registered listeners
     *
     * @param KEventPublisherInterface $publisher The event dispatcher
     * @return void
     */
    public function unsubscribe(KEventPublisherInterface $publisher);

    /**
     * Check if the subscriber is already subscribed to the dispatcher
     *
     * @param  KEventPublisherInterface $publisher  The event dispatcher
     * @return boolean TRUE if the subscriber is already subscribed to the dispatcher. FALSE otherwise.
     */
    public function isSubscribed(KEventPublisherInterface $publisher);

    /**
     * Get the event listeners
     *
     * @return array
     */
    public static function getEventListeners();

    /**
     * Get the priority of the subscriber
     *
     * @return	integer The subscriber priority
     */
    public function getPriority();
}
