<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Model Entity
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Model\Entity
 */
abstract class KModelEntityAbstract extends KObjectArray implements KModelEntityInterface
{
    /**
     * List of computed properties
     *
     * @var array
     */
    private $__computed_properties;

    /**
     * Tracks if entity data is new
     *
     * @var bool
     */
    private $__new = true;

    /**
     * List of modified properties
     *
     * @var array
     */
    protected $_modified = array();

    /**
     * The status
     *
     * Available entity status values are defined as STATUS_ constants
     *
     * @var string
     * @see Database
     */
    protected $_status = null;

    /**
     * The status message
     *
     * @var string
     */
    protected $_status_message = '';

    /**
     * The identity key
     *
     * @var string
     */
    protected $_identity_key;

    /**
     * Constructor
     *
     * @param  KObjectConfig $config  An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_identity_key = $config->identity_key;

        // Reset the entity
        $this->reset();

        //Set the status
        if (isset($config->status)) {
            $this->setStatus($config->status);
        }

        // Set the entity data
        if (isset($config->data)) {
            $this->setProperties($config->data->toArray(), $this->isNew());
        }

        //Set the status message
        if (!empty($config->status_message)) {
            $this->setStatusMessage($config->status_message);
        }
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config An optional KObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'data'            => null,
            'status'          => null,
            'status_message'  => '',
            'identity_key'    => null
        ));

        parent::_initialize($config);
    }

    /**
     * Saves the entity to the data store
     *
     * @return boolean  If successful return TRUE, otherwise FALSE
     */
    public function save()
    {
        if (!$this->isNew()) {
            $this->setStatus(self::STATUS_UPDATED);
        } else {
            $this->setStatus(self::STATUS_CREATED);
        }

        $this->_modified = array();
        return false;
    }

    /**
     * Deletes the entity form the data store
     *
     * @return boolean  If successful return TRUE, otherwise FALSE
     */
    public function delete()
    {
        $this->setStatus(self::STATUS_DELETED);
        return false;
    }

    /**
     * Resets to the default properties
     *
     * @return KModelEntityAbstract
     */
    public function reset()
    {
        $this->_data     = array();
        $this->_modified = array();

        return $this;
    }

    /**
     * Gets the identity key
     *
     * @return string
     */
    public function getIdentityKey()
    {
        return $this->_identity_key;
    }
    /**
     * Get a property
     *
     * Method provides support for computed properties by calling an getProperty[CamelizedName] if it exists. The getter
     * should return the computed value to get.
     *
     * @param   string  $name The property name
     * @return  mixed   The property value.
     */
    public function getProperty($name)
    {
        //Handle computed properties
        if(!$this->hasProperty($name) && !empty($name))
        {
            $getter  = 'getProperty'.KStringInflector::camelize($name);
            $methods = $this->getMethods();

            if(isset($methods[$getter])) {
                parent::offsetSet($name, $this->$getter());
            }
        }

        return parent::offsetGet($name);
    }

    /**
     * Set a property
     *
     * If the value is the same as the current value and the entity is loaded from the data store the value will not be
     * set. If the entity is new the value will be (re)set and marked as modified.
     *
     * Method provides support for computed properties by calling an setProperty[CamelizedName] if it exists. The setter
     * should return the computed value to set.
     *
     * @param   string  $name       The property name.
     * @param   mixed   $value      The property value.
     * @param   boolean $modified   If TRUE, update the modified information for the property
     *
     * @return  $this
     */
    public function setProperty($name, $value, $modified = true)
    {
        if (!array_key_exists($name, $this->_data) || ($this->_data[$name] != $value))
        {
            $computed = $this->getComputedProperties();
            if(!in_array($name, $computed))
            {
                //Force computed properties to re-calculate
                foreach($computed as $property) {
                    parent::offsetUnset($property);
                }

                //Call the setter if it exists
                $setter  = 'setProperty'.KStringInflector::camelize($name);
                $methods = $this->getMethods();

                if(isset($methods[$setter])) {
                    $value = $this->$setter($value);
                }

                //Set the property value
                parent::offsetSet($name, $value);

                //Mark the property as modified
                if($modified || $this->isNew()) {
                    $this->_modified[$name] = $name;
                }
            }
        }

        return $this;
    }

    /**
     * Test existence of a property
     *
     * @param  string  $name The property name.
     * @return boolean
     */
    public function hasProperty($name)
    {
        return parent::offsetExists($name);
    }

    /**
     * Remove a property
     *
     * @param   string  $name The property name.
     * @return  KModelEntityAbstract
     */
    public function removeProperty($name)
    {
        parent::offsetUnset($name);
        unset($this->_modified[$name]);

        return $this;
    }

    /**
     * Get the properties
     *
     * @param   boolean  $modified If TRUE, only return the modified data.
     * @return  array   An associative array of the entity properties
     */
    public function getProperties($modified = false)
    {
        $properties = $this->_data;

        if ($modified) {
            $properties = array_intersect_key($properties, $this->_modified);
        }

        return $properties;
    }

    /**
     * Set the properties
     *
     * @param   mixed   $properties  Either and associative array, an object or a KModelEntityInterface
     * @param   boolean $modified    If TRUE, update the modified information for each property being set.
     * @return  $this
     */
    public function setProperties($properties, $modified = true)
    {
        if ($properties instanceof KModelEntityInterface) {
            $properties = $properties->getProperties(false);
        } else {
            $properties = (array) $properties;
        }

        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value, $modified);
        }

        return $this;
    }

    /**
     * Get a list of the computed properties
     *
     * @return array An array
     */
    public function getComputedProperties()
    {
        if (!$this->__computed_properties)
        {
            $properties = array();

            foreach ($this->getMethods() as $method)
            {
                if (substr($method, 0, 11) == 'getProperty' && $method !== 'getProperty')
                {
                    $property = KStringInflector::underscore(substr($method, 11));
                    $properties[$property] = $property;
                }
            }

            $this->__computed_properties = $properties;
        }

        return $this->__computed_properties;
    }

    /**
     * Returns the status
     *
     * @return string The status
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Set the status
     *
     * @param   string|null  $status The status value or NULL to reset the status
     * @return  KModelEntityAbstract
     */
    public function setStatus($status)
    {
        if($status === KDatabase::STATUS_CREATED) {
            $this->__new = false;
        }

        if($status === KDatabase::STATUS_DELETED) {
            $this->__new = true;
        }

        if($status === KDatabase::STATUS_FETCHED) {
            $this->__new = false;
        }

        $this->_status = $status;
        return $this;
    }

    /**
     * Returns the status message
     *
     * @return string The status message
     */
    public function getStatusMessage()
    {
        return $this->_status_message;
    }

    /**
     * Set the status message
     *
     * @param   string $message The status message
     * @return  KModelEntityAbstract
     */
    public function setStatusMessage($message)
    {
        $this->_status_message = $message;
        return $this;
    }

    /**
     * Get a handle for this object
     *
     * This function returns an unique identifier for the object. This id can be used as a hash key for storing objects
     * or for identifying an object
     *
     * @return string A string that is unique
     */
    public function getHandle()
    {
        if (isset($this->_identity_key)) {
            $handle = $this->getProperty($this->_identity_key);
        } else {
            $handle = parent::getHandle();
        }

        return $handle;
    }

    /**
     * Get a new iterator
     *
     * @return  \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator(array($this));
    }

    /**
     * Checks if the entity is new or not
     *
     * @return bool
     */
    public function isNew()
    {
        return (bool) $this->__new;
    }

    /**
     * Check if a the entity or specific entity property has been modified.
     *
     * If a specific property name is giving method will return TRUE only if this property was modified.
     *
     * @param   string $property The property name
     * @return  boolean
     */
    public function isModified($property = null)
    {
        $result = false;

        if($property)
        {
            if (isset($this->_modified[$property]) && $this->_modified[$property]) {
                $result = true;
            }
        }
        else $result = (bool) count($this->_modified);

        return $result;
    }

    /**
     * Test if the entity is connected to a data store
     *
     * @return	bool
     */
    public function isConnected()
    {
        return false;
    }

    /**
     * Return an associative array of the data
     *
     * Skip the properties that start with an underscore as they are considered private
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        foreach(array_keys($data) as $key)
        {
            if (substr($key, 0, 1) === '_') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Set a property
     *
     * @param   string  $property   The property name.
     * @param   mixed   $value      The property value.
     * @return  void
     */
    #[\ReturnTypeWillChange]
    final public function offsetSet($property, $value)
    {
        $this->setProperty($property, $value);
    }

    /**
     * Get a property
     *
     * @param   string  $property   The property name.
     * @return  mixed The property value
     */
    #[\ReturnTypeWillChange]
    final public function offsetGet($property)
    {
        return $this->getProperty($property);
    }

    /**
     * Check if a property exists
     *
     * @param   string  $property   The property name.
     * @return  boolean
     */
    #[\ReturnTypeWillChange]
    final public function offsetExists($property)
    {
        return $this->hasProperty($property);
    }

    /**
     * Remove a property
     *
     * @param   string  $property The property name.
     * @return  void
     */
    #[\ReturnTypeWillChange]
    final public function offsetUnset($property)
    {
        $this->removeProperty($property);
    }
}