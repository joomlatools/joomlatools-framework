<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Database table for Joomla extensions
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\Database\Table
 */
class ComKoowaDatabaseTableExtensions extends KDatabaseTableAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append([
            'name' => 'extensions',
            'behaviors' => ['parameterizable'],
            'column_map' => [
                'parameters' => 'params',
            ],
            'filters' => [
                'parameters' => ['json']
            ]
        ]);

        parent::_initialize($config);
    }
}