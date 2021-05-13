<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher Route
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Router
 */
class ComKoowaDispatcherRouterRoute extends KDispatcherRouterRoute
{
    /**
     * The route application name
     *
     * @var string
     */
    protected $_application;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->setApplication($config->application);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'application'  => $this->getObject('joomla')->app->getName()
        ));

        parent::_initialize($config);
    }

    public function setApplication($application)
    {
        $this->_application = $application;

        return $this;
    }

    public function getApplication()
    {
        return $this->_application;
    }

    public function toString($parts = self::FULL, $escape = null)
    {
        $query  = $this->getQuery(true);
        $escape = isset($escape) ? $escape : $this->_escape;

        //Add the option to the query for compatibility with the Joomla router
        if(isset($query['component']))
        {
            if(!isset($query['option'])) {
                $query['option'] = 'com_'.$query['component'];
            }

            unset($query['component']);
        }

        //Push option and view to the beginning of the array for easy to read URLs
        $query = array_merge(array('option' => null, 'view'   => null), $query);

        $route = $this->_getRoute($query, $escape);

        //Create a fully qualified route
        if(!empty($this->host) && !empty($this->scheme)) {
            $route = parent::toString(self::AUTHORITY) . '/' . ltrim($route, '/');
        }

        return $route;
    }

    /**
     * Route getter.
     *
     * @param array $query An array containing query variables.
     * @param boolean|null $escape  If TRUE escapes '&' to '&amp;' for xml compliance. If NULL use the default.
     *
     * @return string The route.
     */
    protected function _getRoute($query, $escape)
    {
        $joomla = $this->getObject('joomla');

        // Joomla 4 is not always pushing Itemid to the query
        if ($joomla->isVersion4() && $joomla->app->input->exists('Itemid')) {
            $query['Itemid'] = $joomla->app->input->getInt('Itemid');
        }

        return $joomla->route($this->getApplication(), 'index.php?'.http_build_query($query, '', '&'), $escape);
    }
}