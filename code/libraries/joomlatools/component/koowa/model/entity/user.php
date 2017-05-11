<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * User entity
 *
 * @package Koowa\Component\Koowa\Model
 */
class ComKoowaModelEntityUser extends KModelEntityRow
{
    /**
     * A whitelist of fields visible in the JSON representation
     *
     * @var array
     */
    protected $_fields = array();

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        // Only allow fields in the config option for security reasons
        $this->_fields = array_fill_keys(KObjectConfig::unbox($config->fields), null);
    }

    protected function _initialize(KObjectConfig $config)
    {
        if (empty($config->fields)) {
            $config->fields = array('id', 'name');
        }

        parent::_initialize($config);
    }

    /**
     * Excludes private fields from JSON representation
     *
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();

        $data = array_intersect_key($data, $this->_fields);

        return $data;
    }
}