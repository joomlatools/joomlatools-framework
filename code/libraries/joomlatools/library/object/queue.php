<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Object Queue
 *
 * A queue a data type or collection in which the entities in the collection are kept in order and the principal
 * (or only) operations on the collection are the addition of entities to the rear terminal position, known as
 * enqueue, and removal of entities from the front terminal position, known as dequeue. This makes the queue a
 * First-In-First-Out (FIFO) data structure.
 *
 * Additionally each element can have a "priority" associated with it prioritising the order of the element in the
 * queue. An element with high priority is served before an element with low priority. If two elements have the same
 * priority, they are served according to their order in the queue.
 *
 * @link http://en.wikipedia.org/wiki/Queue_(abstract_data_type)
 * @link http://en.wikipedia.org/wiki/Priority_queue
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object
 * @see     http://www.php.net/manual/en/class.splpriorityqueue.php
 */
class KObjectQueue extends KObject implements Iterator, Countable
{
    /**
     * Object list
     *
     * @var array
     */
    private $__object_list = [];

    /**
     * Priority list
     *
     * @var array
     */
    private $__priority_list = [];

    /**
     * Identifier list
     *
     * @var array
     */
    private $__identifier_list = [];

    /**
     * Inserts an object to the queue.
     *
     * @param   KObjectHandlable $object    A KObject instance
     * @param   integer          $priority  The associated priority
     * @return  boolean TRUE on success FALSE on failure
     */
    public function enqueue( KObjectHandlable $object, $priority)
    {
        $result = false;

        if($handle = $object->getHandle())
        {
            $this->__object_list[$handle] = $object;
            $this->__priority_list[$handle] = $priority;

            asort($this->__priority_list);

            if($object instanceof KObjectInterface) {
                $this->__identifier_list[$handle] = $object->getIdentifier();
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Removes an object from the queue
     *
     * @param   KObjectHandlable $object A KObject instance
     * @return  boolean TRUE on success FALSE on failure
     */
    public function dequeue( KObjectHandlable $object)
    {
        $result = false;

        if($handle = $object->getHandle())
        {
            if(isset($this->__object_list[$handle]))
            {
                unset($this->__object_list[$handle]);
                unset($this->__priority_list[$handle]);

                if($object instanceof KObjectInterface) {
                    unset($this->__identifier_list[$handle]);
                }

                $result = true;
            }
        }

        return $result;
    }

    /**
     * Set the priority of an object in the queue
     *
     * @param KObjectHandlable $object    A command object
     * @param integer          $priority  The priority
     * @return KObjectQueue
     */
    public function setPriority(KObjectHandlable $object, $priority)
    {
        if($handle = $object->getHandle())
        {
            if(isset($this->__priority_list[$handle]))
            {
                $this->__priority_list[$handle] = $priority;

                asort($this->__priority_list);
            }
        }

        return $this;
    }

    /**
     * Get the priority of an object in the queue
     *
     * @param   KObjectHandlable $object A KObject instance
     * @return  integer|boolean The command priority or FALSE if the command isn't enqueued
     */
    public function getPriority(KObjectHandlable $object)
    {
        $result = false;

        if($handle = $object->getHandle())
        {
            if(isset($this->__priority_list[$handle])) {
                $result = $this->__priority_list[$handle];
            }
        }

        return $result;
    }

    /**
     * Check if the queue has an item with the given priority
     *
     * @param  int 	$priority The priority to search for
     * @return boolean
     */
    public function hasPriority($priority)
    {
        return in_array($priority, $this->__priority_list);
    }

    /**
     * Check if the queue has an item with the given identifier
     *
     * @param  mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @return boolean
     */
    public function hasIdentifier($identifier)
    {
        if(!$identifier instanceof KObjectIdentifierInterface) {
            $identifier = $this->getIdentifier($identifier);
        }

        return in_array((string) $identifier, $this->__identifier_list);
    }

    /**
     * Check if the queue contains a given object
     *
     * @param KObjectHandlable $object
     * @return bool
     */
    public function contains(KObjectHandlable $object)
    {
        $result = false;

        if($handle = $object->getHandle()) {
            $result = isset($this->__object_list[$handle]);
        }

        return $result;
    }

    /**
     * Returns the number of elements in the queue
     *
     * Required by the Countable interface
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->__object_list);
    }

    /**
     * Rewind the Iterator to the top
     *
     * Required by the Iterator interface
     *
     * @return  object KObjectQueue
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->__object_list);
        reset($this->__priority_list);

        return $this;
    }

    /**
     * Check whether the queue contains more object
     *
     * Required by the Iterator interface
     *
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !is_null(key($this->__priority_list));
    }

    /**
     * Return current object index
     *
     * Required by the Iterator interface
     *
     * @return	mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->__priority_list);
    }

    /**
     * Return current object pointed by the iterator
     *
     * Required by the Iterator interface
     *
     * @return  mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->__object_list[$this->key()];
    }

    /**
     * Move to the next object
     *
     * Required by the Iterator interface
     *
     * @return	mixed
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->__priority_list);
    }

    /**
     * Return the object from the top of the queue
     *
     * @return  KObject or NULL is queue is empty
     */
    public function top()
    {
        $handles = array_keys((array)$this->__priority_list);

        $object = null;
        if(isset($handles[0])) {
            $object  = $this->__object_list[$handles[0]];
        }

        return $object;
    }

    /**
     * Checks whether the queue is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !count($this->__object_list);
    }

    /**
     * Return an array representing the queue
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $item) {
            $array[] = $item;
        }

        return $array;
    }
}
