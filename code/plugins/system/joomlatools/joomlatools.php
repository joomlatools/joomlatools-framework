<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Joomlatools System Plugin
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Plugin\System\Joomlatools
 */
class PlgSystemJoomlatools extends \Joomla\CMS\Plugin\CMSPlugin
{
    /**
     * Boots Koowa framework and applies some bug fixes for certain environments
     *
     * @param object $subject
     * @param array  $config
     */
    public function __construct($subject, $config = array())
    {
        // Try to raise Xdebug nesting level
        @ini_set('xdebug.max_nesting_level', 200);

        // Set pcre.backtrack_limit to a larger value
        // See: https://bugs.php.net/bug.php?id=40846
        if (version_compare(PHP_VERSION, '5.3.6', '<=') && @ini_get('pcre.backtrack_limit') < 1000000) {
            @ini_set('pcre.backtrack_limit', 1000000);
        }

        //Bugfix: Set offset according to user's timezone
        if (!\Joomla\CMS\Factory::getUser()->guest)
        {
            if ($offset = \Joomla\CMS\Factory::getUser()->getParam('timezone')) {
                \Joomla\CMS\Factory::getConfig()->set('offset', $offset);
            }
        }

        //Bugfix: Set display_errors accordingly
        if(Joomla\CMS\Factory::getConfig()->get('error_reporting') == 'none') {
            @ini_set('display_errors', 0);
        }

        if (static::hasCompatPlugin()) {

            //Bootstrap the Koowa Framework
            $this->bootstrap();

            $this->onAfterKoowaBootstrap();
        }


        parent::__construct($subject, $config);
    }

    public static function hasCompatPlugin()
    {
        if (version_compare(JVERSION, '5.0', '<')) {
            return true;
        }

        return version_compare(JVERSION, '6.0', '<') ? 
            Joomla\CMS\Plugin\PluginHelper::isEnabled('behaviour', 'compat') : 
            Joomla\CMS\Plugin\PluginHelper::isEnabled('behaviour', 'compat6');
    }

    /**
     * Allow event listeners to perform cleanup operations before the application terminates
     */
    public function __destruct()
    {
        $this->onBeforeApplicationTerminate();
    }

    /**
     * Bootstrap the Koowa Framework
     *
     * @return bool Returns TRUE if the framework was found and bootstrapped succesfully.
     */
    public function bootstrap()
    {
        $path = JPATH_LIBRARIES.'/joomlatools/library/koowa.php';
        if (file_exists($path))
        {
            /**
             * Koowa Bootstrapping
             *
             * If KOOWA is defined assume it was already loaded and bootstrapped
             */
            if (!defined('KOOWA'))
            {
                require_once $path;

                $application = Joomla\CMS\Factory::getApplication()->getName();

                /**
                 * Find Composer Vendor Directory
                 */
                $vendor_path = false;
                if(file_exists(JPATH_ROOT.'/composer.json'))
                {
                    $content  = file_get_contents(JPATH_ROOT.'/composer.json');
                    $composer = json_decode($content);

                    if(isset($composer->config->vendor_dir)) {
                        $vendor_path = JPATH_ROOT.'/'.$composer->config->vendor_dir;
                    } else {
                        $vendor_path = JPATH_ROOT.'/vendor';
                    }
                }

                /**
                 * Framework Bootstrapping
                 */
                Koowa::getInstance(array(
                    'cache'           => false, //Joomla\CMS\Factory::getConfig()->get('caching')
                    'cache_namespace' => 'koowa-' . $application . '-' . md5(Joomla\CMS\Factory::getConfig()->get('secret')),
                    'root_path'       => JPATH_ROOT,
                    'base_path'       => JPATH_BASE,
                    'vendor_path'     => $vendor_path
                ));

                /**
                 * Component Bootstrapping
                 */
                $bootstrapper = KObjectManager::getInstance()->getObject('object.bootstrapper')
                    ->registerComponents(JPATH_LIBRARIES . '/joomlatools/component', 'koowa')
                    ->registerApplication('site', JPATH_SITE . '/components', Joomla\CMS\Factory::getApplication()->isClient('site'))
                    ->registerApplication('admin', JPATH_ADMINISTRATOR . '/components', Joomla\CMS\Factory::getApplication()->isClient('administrator'));

                if (is_dir(JPATH_LIBRARIES . '/joomlatools-components')) {
                    $bootstrapper->registerComponents(JPATH_LIBRARIES . '/joomlatools-components', 'koowa');
                }

                $bootstrapper->bootstrap();
            }

            $manager = KObjectManager::getInstance();
            $loader  = $manager->getClassLoader();

            //Module Locator
            $loader->registerLocator(new ComKoowaClassLocatorModule(array(
                'namespaces' => array(
                    '\\'     => JPATH_BASE.'/modules',
                    'Koowa'  => JPATH_LIBRARIES.'/joomlatools/module',
                )
            )));

            /**
             * Module Bootstrapping
             */
            $manager->registerLocator('com:koowa.object.locator.module');

            /**
             * Plugin Bootstrapping
             */
            $loader->registerLocator(new ComKoowaClassLocatorPlugin(array(
                'namespaces' => array(
                    '\\'     => JPATH_PLUGINS,
                    'Koowa'  => JPATH_LIBRARIES.'/joomlatools/plugin',
                )
            )));

            $manager->registerLocator('com:koowa.object.locator.plugin');

            /**
             * Context Boostrapping
             */
            $request = $manager->getObject('request');

            // Get the URL from Joomla if live_site is set
            if (Joomla\CMS\Factory::getConfig()->get('live_site'))
            {
                $request->setBasePath(rtrim(\Joomla\CMS\Uri\Uri::base(true), '/\\'));
                $request->setBaseUrl($manager->getObject('lib:http.url', array('url' => rtrim(\Joomla\CMS\Uri\Uri::base(), '/\\'))));
            }

            /**
             * Plugin Bootstrapping
             */
            Joomla\CMS\Plugin\PluginHelper::importPlugin('koowa', null, true);

            // Load and bootstrap custom vendor directory if it exists
            $custom_vendor = dirname(dirname($path)).'/vendor';
            if (is_dir($custom_vendor) && file_exists($custom_vendor.'/autoload.php')) {
                require_once $custom_vendor.'/autoload.php';
            }

            //Catch all Joomla v3.x exceptions
            if(class_exists('JError') && !version_compare(JVERSION, 4, '>=')) {
                JError::setErrorHandling(E_ERROR, 'callback', array($this, 'onErrorJ3'));
            }

            return true;
        }

        return false;
    }

    /**
     * Proxy onAfterKoowaBootstrap
     *
     * @return void
     */
    public function onAfterKoowaBootstrap()
    {
        $this->_proxyEvent('onAfterKoowaBootstrap');
    }

	/**
	 * On after logout event handler
	 *
	 * @param array $options
	 * @return void
	 */
	public function onUserAfterLogout($options)
	{
		$this->_proxyEvent('onUserAfterLogout', $options);
	}

    /**
     * Proxy onAfterInitialise
     *
     * @return void
     */
    public function onAfterInitialise()
    {
        $this->_proxyEvent('onAfterApplicationInitialise');
    }

    /**
     * Proxy onAfterRoute
     *
     * @return void
     */
    public function onAfterRoute()
    {
        $this->_proxyEvent('onAfterApplicationRoute');
    }

    /**
     * Proxy onAfterDispatch
     *
     * @return void
     */
    public function onAfterDispatch()
    {
        $this->_proxyEvent('onAfterApplicationDispatch');
    }

    /**
     * Proxy onBeforeRender
     *
     * @return void
     */
    public function onBeforeRender()
    {
        $this->_proxyEvent('onBeforeApplicationRender');
    }

    /**
     * Proxy exceptions
     *
     * Joomla 4 catches exceptions in CMSApplication::execute and dispatches onError()
     *
     * @see: https://github.com/joomla/joomla-cms/blob/4.0-dev/libraries/src/Application/CMSApplication.php#L296
     * @return void
     */
    public function onError(Joomla\CMS\Event\ErrorEvent $event)
    {
        $exception = $event->getError();
        $is_db_error = ($exception instanceof KDatabaseException) || ($exception instanceof mysqli_sql_exception);

        if ($is_db_error && !JDEBUG) {
            $exception = new \RuntimeException('A database error has occurred. Please enable debug mode for more information.', 500, $exception);
            $event->setError($exception);
        }
        
        if ($exception instanceof \Throwable && class_exists('Koowa')) {
            Koowa::getObject('exception.handler')->handleException($exception);
        }
    }

    /**
     * Proxy exceptions
     *
     * Joomla 3 exceptions are forwarded through the onError() callback 
     * registered in the bootstrap method
     *
     * @return void
     */
    public function onErrorJ3($exception)
    {
        if ($exception instanceof \Throwable) {
            Koowa::getObject('exception.handler')->handleException($exception);
        }
    }

    /**
     * Proxy onBeforeRender
     *
     * @return void
     */
    public function onBeforeCompileHead()
    {
        $this->_proxyEvent('onBeforeApplicationCompileHead');
    }

    /**
     * Proxy onAfterRender
     *
     * @return void
     */
    public function onAfterRender()
    {
        $this->_proxyEvent('onAfterApplicationRender');
    }

    /**
     * Proxy onAfterRespond
     *
     * @return void
     */
    public function onAfterRespond()
    {
        $this->_proxyEvent('onAfterApplicationRespond');
    }

    /**
     * Proxy onBeforeApplicationTerminate
     *
     * @return void
     */
    public function onBeforeApplicationTerminate()
    {
        $this->_proxyEvent('onBeforeApplicationTerminate');
    }

    /**
     * Proxy all Joomla events
     *
     * @param   array  &$args  Arguments
     * @return  mixed  Routine return value
     */
    protected function _proxyEvent($event, $args = array())
    {
        $result = null;

        //Publish the event
        if (class_exists('Koowa')) {
            $result = Koowa::getObject('event.publisher')->publishEvent($event, $args, Joomla\CMS\Factory::getApplication());
        }

        return $result;
    }
}
