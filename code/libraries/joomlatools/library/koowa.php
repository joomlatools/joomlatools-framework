<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Koowa constant, if true koowa is loaded
 */
define('KOOWA', 1);

/**
 * Koowa Framework Loader
 *
 * Loads classes and files, and provides metadata for Koowa such as version info
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library
 */
class Koowa
{
    /**
     * Koowa version
     *
     * @var string
     */
    const VERSION = '4.0.10';


    /**
     * Debug state
     *
     * @var boolean
     */
    protected static $_debug;

    /**
     * The root path
     *
     * @var string
     */
    protected $_root_path;

    /**
     * The base path
     *
     * @var string
     */
    protected $_base_path;

    /**
     * The vendor path
     *
     * @var string
     */
    protected $_vendor_path;

    /**
     * The object manager
     *
     * @var KObjectManager
     */
    private static $__object_manager;

    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the constructor private
     *
     * @param  array  $config An optional array with configuration options.
     */
    private function __construct($config = array())
    {
        if(isset($config['debug'])) {
            self::$_debug = $config['debug'];
        } else {
            self::$_debug = (getenv('KOOWA_DEBUG') !== false ? filter_var( getenv('KOOWA_DEBUG') , FILTER_VALIDATE_BOOLEAN) : false);
        }

        //Initialize the root path
        if(isset($config['root_path'])) {
            $this->_root_path = $config['root_path'];
        } else {
            $this->_root_path = realpath($_SERVER['DOCUMENT_ROOT']);
        }

        //Initialize the base path
        if(isset($config['base_path'])) {
            $this->_base_path = $config['base_path'];
        } else {
            $this->_base_path = $this->_root_path;
        }

        //Initialize the vendor path
        if(isset($config['vendor_path'])) {
            $this->_vendor_path = $config['vendor_path'];
        } else {
            $this->_vendor_path = $this->_root_path.'/libraries/vendor';
        }

        //Load functions
        require_once dirname(__FILE__).'/functions.php';

        //Load the legacy functions
        require_once dirname(__FILE__).'/legacy.php';

        //Setup the loader
        require_once dirname(__FILE__).'/class/loader.php';

        if (!isset($config['class_loader'])) {
            $config['class_loader'] = KClassLoader::getInstance($config);
        }

        //Setup the factory
        $manager = KObjectManager::getInstance($config);

        //Register the component class locator
        $manager->getClassLoader()->registerLocator(new KClassLocatorComponent(
            array(
                'namespaces' => array(
                    '\\'    => $this->_base_path.'/components',
                    'Koowa' => dirname(dirname(__FILE__))
                )
            )
        ));

        //Register the component object locator
        $manager->registerLocator('lib:object.locator.component');

        //Register the composer class locator
        if(file_exists($this->getVendorPath()))
        {
            $manager->getClassLoader()->registerLocator(new KClassLocatorComposer(
                array(
                    'vendor_path' => $this->getVendorPath()
                )
            ));
        }

        //Warm-up the stream factory
        $manager->getObject('lib:filesystem.stream.factory');

        //Store the object manager
        self::$__object_manager = $manager;
    }

    /**
     * Clone
     *
     * Prevent creating clones of this class
     */
    private function __clone() { }

    /**
     * Singleton instance
     *
     * @param  array  $config An optional array with configuration options.
     * @return Koowa
     */
    final public static function getInstance($config = array())
    {
        static $instance;

        if ($instance === NULL) {
            $instance = new self($config);
        }

        return $instance;
    }

    /**
     * Get the framework version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Get vendor path
     *
     * @return string
     */
    public function getVendorPath()
    {
        return $this->_vendor_path;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->_root_path;
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_base_path;
    }

    /**
     * Enable or disable debug
     *
     * @param bool $debug True or false.
     * @return bool
     */
    public static function setDebug($debug)
    {
        return self::$_debug = (bool) $debug;
    }

    /**
     * Check if debug is enabled
     *
     * @return bool
     */
    public static function isDebug()
    {
        return self::$_debug;
    }

    /**
     * Proxy static method calls to the object manager
     *
     * @param  string     $method    The function name
     * @param  array      $arguments The function arguments
     * @throws \BadMethodCallException  If method is called statically before Kodekit has been instantiated.
     * @return mixed The result of the method
     */
    public static function __callStatic($method, $arguments)
    {
        if(self::$__object_manager instanceof KObjectManager) {
            return self::$__object_manager->$method(...$arguments);
        }
        else throw new \BadMethodCallException('Cannot call method: $s. Koowa has not been instantiated', $method);
    }
}
