<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Wrapper Template Filter
 *
 * Filter for wrapping a template output
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Filter
 */
class KTemplateFilterWrapper extends KTemplateFilterAbstract
{
    /**
     * An sprintf parameter with %s in it for the template content
     *
     * @type string
     */
    protected $_wrapper;

    /**
     * @param KObjectConfig $config
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->setWrapper($config->wrapper);
    }

    /**
     * @param KObjectConfig $config
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'  => self::PRIORITY_LOWEST,
            'wrapper' => null
        ));

        parent::_initialize($config);
    }

    /**
     * Checks if the text has <ktml:template:wrapper> to make sure it only runs once
     *
     * @param $text
     * @return $this
     */
    public function filter(&$text)
    {
        if ($this->getWrapper() && strpos($text, '<ktml:template:wrapper>') !== false)
        {
            $text = sprintf($this->getWrapper(), $text);
            $text = str_replace('<ktml:template:wrapper>', '', $text);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getWrapper()
    {
        return $this->_wrapper;
    }

    /**
     * @param $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->_wrapper = $wrapper;
    }
}
