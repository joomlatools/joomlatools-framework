<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Default Dispatcher Router
 *
 * Provides route building and parsing functionality
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Router
 */
class KDispatcherRouter extends KObject implements KDispatcherRouterInterface, KObjectMultiton
{
    /**
     * Function to convert a route to an internal URI
     *
     * @param   KHttpUrlInterface  $url  The url.
     * @return  boolean
     */
    public function parse(KHttpUrlInterface $url)
    {
        return true;
    }

    /**
     * Function to convert an internal URI to a route
     *
     * @param	KHttpUrlInterface   $url	The internal URL
     * @return	boolean
     */
    public function build(KHttpUrlInterface $url)
    {
        return true;
    }
}
