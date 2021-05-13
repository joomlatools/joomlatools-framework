<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Alias Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Filter
 */
class ComKoowaFilterAlias extends KFilterSlug implements KFilterTraversable
{

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'length'    => 512
        ));

        parent::_initialize($config);
    }

    /**
     * Validate a value
     *
     * @param   string  $value Variable to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * Sanitize a value
     *
     * @param   string $value Variable to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        $value = $this->getObject('joomla')->applicationHelper->stringURLSafe($value);

        //limit length
        if (mb_strlen($value) > $this->_length) {
            $value = mb_substr($value, 0, $this->_length);
        }

        return $value;
    }
}
