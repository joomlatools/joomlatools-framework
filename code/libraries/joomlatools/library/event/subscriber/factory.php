<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event Subscriber Factory
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Event|Subscriber
 */
class KEventSubscriberFactory extends KObject implements KObjectSingleton
{
    /**
     * List of event subscribers
     *
     * Associative array of event subscribers, where key holds the subscriber identifier string
     * and the value is an identifier object.
     *
     * @var  array
     */
    private $__subscribers = array();

    /**
     * List of event listeners
     *
     * Associative array of event listeners, where key holds the event name and the value is
     * an identifier object.
     *
     * @var  array
     */
    private $__listeners = array();

    /**
     * Object constructor
     *
     * @param KObjectConfig $config An optional ObjectConfig object with configuration options
     * @throws InvalidArgumentException
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Add the event subscribers
        $subscribers = (array) KObjectConfig::unbox($config->subscribers);

        foreach ($subscribers as $key => $value)
        {
            if (is_numeric($key)) {
                $this->registerSubscriber($value);
            } else {
                $this->registerSubscriber($key, $value);
            }
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config  An optional ObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'subscribers' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Register an subscriber
     *
     * @param string $identifier A subscriber identifier string
     * @param  array $config  An optional associative array of configuration options
     * @throws UnexpectedValueException
     * @return bool Returns TRUE on success, FALSE on failure.
     */
    public function registerSubscriber($identifier, array $config = array())
    {
        $result = false;

        //Create the complete identifier if a partial identifier was passed
        if (is_string($identifier) && strpos($identifier, '.') === false)
        {
            $identifier = $this->getIdentifier()->toArray();
            $identifier['path'] = array('event', 'subscriber');
            $identifier['name'] = $identifier;
        }

        $identifier = $this->getIdentifier($identifier);
        $class      = $this->getObject('manager')->getClass($identifier);

        if(!$class || !array_key_exists('KEventSubscriberInterface', class_implements($class)))
        {
            throw new UnexpectedValueException(
                'Event Subscriber: '.$identifier.' does not implement KEventSubscriberInterface'
            );
        }

        if (!isset($this->__subscribers[(string)$identifier]))
        {
            $listeners = $class::getEventListeners();

            if (!empty($listeners))
            {
                $identifier->getConfig()->merge($config);

                foreach($listeners as $listener) {
                    $this->__listeners[$listener][] = $identifier;
                }
            }

            $this->__subscribers[(string)$identifier] = true;
        }

        return $result;
    }

    /**
     * Instantiate the subscribers for the specified event
     *
     * The subscribers will be created if does not exist yet.
     *
     * @param  mixed $event An object that implements ObjectInterface, ObjectIdentifier object
     *                          or valid identifier string
     * @param  array  $event_publisher   An optional associative array of configuration settings
     * @throws UnexpectedValueException    If the subscriber is not implementing the EventSubscriberInterface
     * @return KEventSubscriberFactory
     */
    public function subscribeEvent($event, $event_publisher)
    {
        foreach($this->getSubscribers($event) as $identifier)
        {
            if(!$this->__subscribers[(string)$identifier] instanceof KEventSubscriberInterface)
            {
                $subscriber = $this->getObject($identifier);
                $subscriber->subscribe($event_publisher);

                $this->__subscribers[(string)$identifier] = $subscriber;
            }
        }

        return $this;
    }

    /**
     * Get the subscribers for a specific event
     *
     * @param string $event The name of the event
     * @return array
     */
    public function getSubscribers($event)
    {
        $result = array();
        if(isset($this->__listeners[$event])) {
            $result = $this->__listeners[$event];
        }

        return $result;
    }
}
