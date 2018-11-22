<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Config Interface
 *
 * ObjectConfig provides a property based interface to an array.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object\Config
 */
interface KObjectConfigInterface extends \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * Get a new instance
     *
     * @return KObjectConfigInterface
     */
    public static function getInstance();

    /**
     * Retrieve a configuration option
     *
     * If the option does not exist return the default.
     *
     * @param string  $name
     * @param mixed   $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Set a configuration option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws \RuntimeException If the config is read only
     * @return KObjectConfigInterface
     */
    public function set($name, $value);

    /**
     * Check if a configuration option exists
     *
     * @param  	string 	$name The configuration option name.
     * @return  boolean
     */
    public function has($name);

    /**
     * Remove a configuration option by name(s)
     *
     * @param   string|array $names The configuration option name or a list of option names.
     * @return  KObjectConfigInterface
     */
    public function remove($names);

    /**
     * Checks if a value exists
     *
     * @param   mixed $needle The searched value
     * @param   bool  $strict If TRUE then check the types of the needle in the haystack.
     * @return  bool Returns TRUE if needle is found in the array, FALSE otherwise.
     */
    public function contains($needle, $strict = false);

    /**
     * Merge options
     *
     * This method will overwrite keys that already exist, keys that don't exist yet will be added.
     *
     * For duplicate keys, the following will be performed:
     *
     * - Nested configs will be recursively merged.
     * - Items in $options with INTEGER keys will be appended.
     * - Items in $options with STRING keys will overwrite current values.
     *
     * @param  array|\Traversable|KObjectConfigInterface  $options A ObjectConfig object an or array of options to be added
     * @return KObjectConfigInterface
     */
    public function merge($options);

    /**
     * Append options
     *
     * This function only adds keys that don't exist and it filters out any duplicate values
     *
     * @param  array|\Traversable|KObjectConfigInterface  $options A ObjectConfigInterface instance an or array of options to be appended
     * @return KObjectConfigInterface
     */
    public function append($options);

    /**
     * Return the data
     *
     * If the data being passed is an instance of ObjectConfigInterface the data will be transformed
     * to an associative array.
     *
     * @param mixed|KObjectConfigInterface $data
     * @return mixed|array
     */
    public static function unbox($data);

    /**
     * Return an associative array of the config data.
     *
     * @return array
     */
    public function toArray();
}