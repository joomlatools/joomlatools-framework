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
            'application'  => JFactory::getApplication()->getName()
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
        $app = JFactory::getApplication();

        $this->_appendItemid($query);

        $query = 'index.php?'.http_build_query($query, '', '&');

        if (class_exists('JRoute'))
        {
            if ($app->getName() !== $this->getApplication()) {
                $query = JRoute::link($this->getApplication(), $query, $escape);
            } else {
                $query = JRoute::_($query, $escape);
            }
        }

        return $query;
    }

    protected function _appendItemid(&$query)
    {
        if (!isset($query['Itemid']) && version_compare(JVERSION, '4', '>='))
        {
            // Mimic Joomla's 3 behavior on Joomla 4

            $app = JFactory::getApplication();

            $input = $app->getInput();

            if ($input->exists('Itemid'))
            {
                $item_id = $input->getInt('Itemid');

                $item = $app->getMenu()->getItem($item_id);

                if (isset($item))
                {
                    if (isset($query['option']))
                    {
                        if ($query['option'] == $item->component) {
                            $query['Itemid'] = $item_id;
                        }
                    }
                    else $query['Itemid'] = $item_id;
                }
            }
        }
    }

    /**
     * Parses a route contained within the current URL object
     *
     * @param array An array containing query variables of the parsed route
     * @retun array|boolean
     */
    public function parse()
    {
        $admin_path = sprintf('%s/%s', $this->getObject('request')->getSiteUrl()->getPath(), 'administrator/');

        if (strpos($this->getPath(), $admin_path) === 0) {
            $client = 'administrator';
        } else {
            $client = 'site';
        }

        $router_class = sprintf('\Joomla\CMS\Router\%sRouter', ucfirst($client));

        if (version_compare(JVERSION, '4', '>='))
        {
            $container = \Joomla\CMS\Factory::getContainer();

            if ($client == 'site') {
                $app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
            } else {
                $app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);
            }

            $menu = $container->get(Joomla\CMS\Menu\MenuFactoryInterface::class)
                              ->createMenu($client, array('app' => $app));

            $router = new $router_class($app, $menu);
        }
        else
        {
            $app_class = sprintf('\Joomla\CMS\Application\%sApplication', ucfirst($client));

            $app = new $app_class();

			// Set application language
			$params = Joomla\CMS\Component\ComponentHelper::getParams('com_languages');
			$language = $params->get($client, $app->get('language', 'en-GB'));
			$app->loadLanguage(Joomla\CMS\Language\Language::getInstance($language, $app->get('debug_lang')));

			$menu_class = sprintf('\Joomla\CMS\Menu\%sMenu', ucfirst($client));

            $menu = new $menu_class(array('app' => $app));

            $router = new $router_class(array('mode' => $app->getCfg('sef')), $app, $menu);
        }

        $uri = new Joomla\Uri\Uri(parent::toString());

        $result = null;

        try {
            $result = $router->parse($uri, false);
        } catch (\Joomla\CMS\Router\Exception\RouteNotFoundException $e) {
            $result = false;
        }

        return $result;
    }
}
