<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Restrictable Database Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Koowa\Database\Behavior
 */
class ComKoowaDatabaseBehaviorRestrictable extends KDatabaseBehaviorAbstract
{
    protected $_actions;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions = KObjectConfig::unbox($config->actions);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(['actions' => []]);
    
        parent::_initialize($config);
    }

    public function isRestrictedAction($action)
    {
        return in_array($action, $this->_actions);
    }
}