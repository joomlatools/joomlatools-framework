<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher Response
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Response
 */
final class ComKoowaDispatcherResponse extends KDispatcherResponse
{
    const CONTEXT_KOOWA  = 'koowa';
    const CONTEXT_JOOMLA = 'joomla';
    const CONTEXT_NONE   = null;

    /**
     * The response context
     *
     * @var string
     */
    protected $_context;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config 	An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_context = $config->context;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	KObjectConfig $config 	An optional ObjectConfig object with configuration options.
     * @return 	void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'context'  => self::CONTEXT_NONE
        ));

        parent::_initialize($config);
    }

    /**
     * The response context
     *
     * @return string
     */
    public function setContext($context)
    {
        $this->_context = $context;
        return $this;
    }

    /**
     * The response context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->_context;
    }
}