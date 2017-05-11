<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Users model that wraps Joomla user data
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Koowa\Model
 */
class ComKoowaModelUsers extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('email'   , 'email', null, true)
            ->insert('username', 'alnum', null, true)
            ->insert('sendEmail', 'int');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'table'     => $this->getIdentifier()->name,
            'behaviors' => array('searchable' => array('columns' => array('name', 'username', 'email')))
        ));

        parent::_initialize($config);
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        $state = $this->getState();

        if (is_numeric($state->sendEmail)) {
            $query->where('sendEmail = :sendEmail')->bind(array('sendEmail' => $state->sendEmail));
        }

        parent::_buildQueryWhere($query);
    }
}