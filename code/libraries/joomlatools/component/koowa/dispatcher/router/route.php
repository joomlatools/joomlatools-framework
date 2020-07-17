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
     * The supported route applications
     *
     * @var array An array containing application names
     */
    protected $_applications;

    /**
     * The route application name
     *
     * @var string
     */
    protected $_application;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_applications = KObjectConfig::unbox($config->applications);
        $this->_application  = $this->setApplication($config->application);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $clients = JApplicationHelper::getClientInfo();

        $applications = array();

        foreach ($clients as $client) {
            $applications[] = $client->name;
        }

        $config->append(array(
            'applications' => $applications,
            'application'  => JFactory::getApplication()->getName()
        ));

        parent::_initialize($config);
    }

    public function setApplication($application)
    {
        if (!in_array($application, $this->_applications)) {
            throw new InvalidArgumentException(sprintf('Wrong application value: "%s". Allowed values are: %s', $application, implode(', ', $this->_applications)));
        }

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
        $current = JFactory::getApplication();

        if ($current->getName() !== $this->getApplication())
        {
            $application = JApplicationCms::getInstance($this->getConfig()->application);

            // Force route application during route build.
            JFactory::$application = $application;

            // Get the router.
            $router = $application->getRouter();

            $url = 'index.php?'.http_build_query($query, '', '&');

            // Build route.
            $route = $router->build($url);

            // Revert application change.
            JFactory::$application = $current;

            $route = $route->toString(array('path', 'query', 'fragment'));

            // Check if we need to remove "administrator" from the path
            if ($current->isClient('administrator') && $application->getName() == 'site')
            {
                $base = JUri::base('true');

                $replacement = explode('/', $base);

                array_pop($replacement);

                $replacement = implode('/', $replacement);

                $base = str_replace('/', '\/', $base);

                $route = preg_replace('/^' . $base . '/', $replacement, $route);
            }

            // Replace spaces.
            $route = preg_replace('/\s/u', '%20', $route);

            if ($escape) {
                $route = htmlspecialchars($route, ENT_COMPAT, 'UTF-8');
            }
        }
        else $route = JRoute::_('index.php?'.http_build_query($query, '', '&'), $escape);

        return $route;
    }
}