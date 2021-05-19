<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Sluggable Database Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Database\Behavior
 */
class ComKoowaDatabaseBehaviorSluggable extends KDatabaseBehaviorSluggable
{
    /**
     * Push the application specific alias filter to the chain
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append([
            'filter' => ['string', 'trim', 'alias']
        ]);

        parent::_initialize($config);
    }
    
    /**
     * Make sure the slug is unique
     *
     * This function checks if the slug already exists and if so appends a number to the slug to make it unique. The
     * slug will get the form of slug-x.
     *
     * If the slug is empty it returns the current date in the format Y-m-d-H-i-s
     *
     * @return void
     */
    protected function _canonicalizeSlug()
    {
        if (trim(str_replace($this->_separator, '', $this->slug)) == '') {
            $this->slug = (new DateTime('now'))->format('Y-m-d-H-i-s');
        }

        parent::_canonicalizeSlug();
    }
}
