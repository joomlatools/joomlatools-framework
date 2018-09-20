<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\View
 */
class ComKoowaViewHtml extends KViewHtml
{
    /**
     * The view decorator
     *
     * @var string
     */
    private $__decorator;

    /**
     * Constructor
     *
     * @param   KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->setDecorator($config->decorator);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'decorator'        => 'joomla',
            'template_filters' => array('version'),
            'template_functions' => array(
                'decorator'   => array($this, 'getDecorator'),
            ),
        ));

        parent::_initialize($config);
    }

    public function getDecorator()
    {
        return $this->__decorator;
    }

    public function setDecorator($decorator)
    {
        $this->__decorator = $decorator;

        return $this;
    }
}
