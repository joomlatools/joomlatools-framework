<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Database Query Interface
 *
 * @author  Gergo Erdosi <https://github.com/gergoerdosi>
 * @package Koowa\Library\Database
 */
interface KDatabaseQueryInterface
{
    /**
     * Bind values to a corresponding named placeholders in the query.
     *
     * @param  array $parameters Associative array of parameters.
     * @return $this
     */
    public function bind(array $parameters);

    /**
     * Get the query parameters
     *
     * @return KObjectArray
     */
    public function getParameters();

    /**
     * Set the query parameters
     *
     * @param array $parameters  The query parameters
     * @return KDatabaseQueryInterface
     */
    public function setParameters(array $parameters);

    /**
     * Gets the database adapter
     *
     * @return \KDatabaseAdapterInterface
     */
    public function getAdapter();
    
    /**
     * Set the database adapter
     *
     * @param  KDatabaseAdapterInterface $adapter A KDatabaseAdapterInterface object
     * @return KDatabaseQueryInterface
     */
    public function setAdapter(KDatabaseAdapterInterface $adapter);
}
