<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

class ComKoowaModelEntityExtension extends KDatabaseRowAbstract
{
    /**
     * Saves the extension data to the data store
     *
     * @see     KDatabaseRowAbstract::save()
     * @return boolean  If successful return TRUE, otherwise FALSE
     */
    public function save()
    {
        $result = parent::save();
        
        if ($result) {
            // Clear the system cache
            JFactory::getCache('_system', 'output')->clean();
        }

        return $result;
    }
}