<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Cache Class Registry
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Class
 */
class KClassRegistryCache extends KClassRegistry
{
    /**
 	 * The registry cache namespace
 	 *
 	 * @var boolean
 	 */
    protected $_namespace = 'koowa-registry-class';

    /**
     * Constructor
     *
     * @throws \RuntimeException    If the APC PHP extension is not enabled or available
     */
    public function __construct()
    {
        if (!extension_loaded('apc')) {
            throw new RuntimeException('Unable to use ObjectRegistryCache as APC is not enabled.');
        }
    }

    /**
     * Get the registry cache namespace
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * Get the registry cache namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

 	/**
     * Get an item from the array by offset
     *
     * @param   int     $offset The offset
     * @return  mixed   The item from the array
     */
    public function offsetGet($offset)
    {
        if(!parent::offsetExists($offset)) {
            $result = apc_fetch($this->_namespace.'-'.$offset);
        } else {
            $result = parent::offsetGet($offset);
        }

        return $result;
    }

    /**
     * Set an item in the array
     *
     * @param   int     $offset The offset of the item
     * @param   mixed   $value  The item's value
     * @return  object  ObjectArray
     */
    public function offsetSet($offset, $value)
    {
        apc_store($this->_namespace.'-'.$offset, $value);

        parent::offsetSet($offset, $value);
    }

	/**
     * Check if the offset exists
     *
     * @param   int   $offset The offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        if(false === $result = parent::offsetExists($offset)) {
            $result = apc_exists($this->_namespace.'-'.$offset);
        }

        return $result;
    }
}