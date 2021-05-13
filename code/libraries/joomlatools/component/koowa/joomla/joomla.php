<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Wrapper around Joomla calls
 *
 * This class wraps Joomla calls in the framework in a class so they can be mocked, replaced, or augmented as needed.
 * For example JApplication instance can be reached with `$this->getObject('joomla')->app or you can get the database
 * driver with `$this->getObject('joomla')->db`
 *
 * Certain Joomla classes with static methods such as `JPluginHelper` or `JHtml` are proxied with anonymous classes.
 * For example `$this->getObject('joomla')->pluginHelper->importPlugin('user');` calls `JPluginHelper::importPlugin`
 *
 * Constants such as `JPATH_ROOT` can be reached with `$this->getObject('joomla')->getPath('root')`
 *
 * Some methods like `isSite`, `isAdmin`, or `isDebug` are provided for convenience.
 *
 * @method   \Joomla\CMS\Application\BaseApplication app($id = null, array $config = array(), $prefix = 'J')
 * @method   \Joomla\CMS\Application\BaseApplication application($id = null, array $config = array(), $prefix = 'J')
 * @method   \Joomla\CMS\Cache\CacheController cache($group = '', $handler = 'callback', $storage = null)
 * @method   \Joomla\CMS\Editor\Editor editor($editor = 'none')
 * @method   string route($url, $xhtml = true, $tls = \Joomla\CMS\Router\Route::TLS_IGNORE, $absolute = false)
 * @method   string translate($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
 * @property \Joomla\CMS\Application\BaseApplication  app
 * @property \Joomla\CMS\Application\BaseApplication  application
 * @property \Joomla\Registry\Registry  config
 * @property \JDatabaseDriver  database
 * @property \JDatabaseDriver  db
 * @property \Joomla\CMS\Document\Document document
 * @property JEventDispatcher eventDispatcher
 * @property \Joomla\CMS\Language\Language language
 * @property \Joomla\Registry\Registry registry
 * @property \Joomla\CMS\Session\Session session
 * @property JUser user
 * @property \Joomla\CMS\Table\User userTable
 */
class ComKoowaJoomla extends KObject implements KObjectSingleton
{
    /**
     * @var \Joomla\CMS\Access\Access
     */
    public $access;

    /**
     * @var \Joomla\CMS\Application\ApplicationHelper
     */
    public $applicationHelper;

    /**
     * @var \Joomla\CMS\Component\ComponentHelper
     */
    public $componentHelper;

    /**
     * @var \Joomla\CMS\Exception\ExceptionHandler
     */
    public $exceptionHandler;

    /**
     * @var \Joomla\CMS\HTML\HTMLHelper
     */
    public $htmlHelper;

    /**
     * @var \Joomla\CMS\Helper\ModuleHelper
     */
    public $moduleHelper;

    /**
     * @var \Joomla\CMS\Plugin\PluginHelper
     */
    public $pluginHelper;

    /**
     * @var \Joomla\CMS\Uri\Uri
     */
    public $uri;

    /**
     * @var \Joomla\CMS\User\UserHelper
     */
    public $userHelper;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->access = $this->__getStaticClassWrapper('\Joomla\CMS\Access\Access');
        $this->applicationHelper = $this->__getStaticClassWrapper('\Joomla\CMS\Application\ApplicationHelper');
        $this->componentHelper = $this->__getStaticClassWrapper('\Joomla\CMS\Component\ComponentHelper');
        $this->exceptionHandler = $this->__getStaticClassWrapper('\Joomla\CMS\Exception\ExceptionHandler');
        $this->htmlHelper = $this->__getStaticClassWrapper('\Joomla\CMS\HTML\HTMLHelper');
        $this->moduleHelper = $this->__getStaticClassWrapper('\Joomla\CMS\Helper\ModuleHelper');
        $this->pluginHelper = $this->__getStaticClassWrapper('\Joomla\CMS\Plugin\PluginHelper');
        $this->uri = $this->__getStaticClassWrapper('\Joomla\CMS\Uri\Uri');
        $this->userHelper = $this->__getStaticClassWrapper('\Joomla\CMS\User\UserHelper');
    }

    /**
     * Returns JPATH_* constants
     *
     * @param string $type
     * @return string
     */
    public function getPath($type)
    {
        switch ($type) {
            case 'root':
                return JPATH_ROOT;
            case 'site':
                return JPATH_SITE;
            case 'admin':
            case 'administrator':
                return JPATH_ADMINISTRATOR;
            case 'themes':
                return JPATH_THEMES;
        }

        throw new \InvalidArgumentException('Unknown path type');
    }

    /**
     * Shortcut for JApplication::isClient('site') as it's removed from Joomla 4
     *
     * @return bool
     */
    public function isSite()
    {
        return $this->app->isClient('site');
    }

    /**
     * Shortcut for JApplication::isClient('administrator') as it's removed from Joomla 4
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->app->isClient('administrator');
    }

    /**
     * Returns true if Joomla version is greater than 4.0
     * @return bool
     */
    public function isVersion4()
    {
        return version_compare($this->getVersion(), '4', '>=');
    }

    /**
     * Returns Joomla version
     *
     * @return string
     */
    public function getVersion()
    {
        return JVERSION;
    }

    /**
     * Returns true if Joomla debug is enabled
     * @return bool
     */
    public function isDebug()
    {
        return JDEBUG;
    }

    public function __get($parameter)
    {
        switch ($parameter) {
            case 'app':
            case 'application':
                return JFactory::getApplication();
            case 'config':
                return JFactory::getConfig();
            case 'database':
            case 'db':
                return JFactory::getDbo();
            case 'document':
                return JFactory::getDocument();
            case 'eventDispatcher':
                return JEventDispatcher::getInstance();
            case 'language':
                return JFactory::getLanguage();
            case 'registry':
                return new JRegistry();
            case 'session':
                return JFactory::getSession();
            case 'uri':
                return JUri::getInstance();
            case 'user':
                return JFactory::getUser();
            case 'userTable':
                return JUser::getTable();
        }

        throw new \UnexpectedValueException('Unknown parameter');
    }

    public function __call($method, $arguments)
    {
        switch ($method) {
            case 'app':
            case 'application':
                return JFactory::getApplication(...$arguments);
            case 'cache':
                return JFactory::getCache(...$arguments);
            case 'editor':
                return JEditor::getInstance(...$arguments);
            case 'route':
                if ($arguments && in_array($arguments[0], ['site', 'administrator'])) {
                    return JRoute::link(...$arguments);
                } else {
                    return JRoute::_(...$arguments);
                }
            case 'translate':
                return JText::_(...$arguments);
            case 'user':
                return JFactory::getUser(...$arguments);
        }

        throw new \UnexpectedValueException('Unknown method');
    }

    /**
     * Creates an anonymous class that redirects method calls as static calls to the target class
     * @param $class
     * @return object
     */
    private function __getStaticClassWrapper($class)
    {
        return new class($class) {
            public $class;
            public function __construct($class)
            {
                $this->class = $class;
            }
            public function __call($method, $parameters) {
                return call_user_func_array([$this->class, $method], $parameters);
            }
        };
    }
}