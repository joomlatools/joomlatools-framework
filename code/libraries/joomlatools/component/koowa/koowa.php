<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

if(!defined('JPATH_ROOT')) {
    define('JPATH_ROOT',  defined('KOOWA_ROOT') ? KOOWA_ROOT : false);
}

if(!defined('JPATH_BASE')) {
    define('JPATH_BASE', defined('KOOWA_BASE') ? KOOWA_BASE : JPATH_ROOT);
}

if(!defined('JPATH_CONFIGURATION')) {
    define('JPATH_CONFIGURATION',  defined('KOOWA_CONFIG') ? KOOWA_CONFIG : JPATH_ROOT.'/config');
}

if(!defined('JPATH_LIBRARIES')) {
    define('JPATH_LIBRARIES', false);
}

if(!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', false);
}

if(!defined('JPATH_SITE')) {
    define('JPATH_SITE', false);
}

if(!class_exists('JFactory'))
{
    class_alias('ComKoowa', 'JFactory');

    //Load the configuration
    JFactory::getConfig();
}

if(!defined('JDEBUG')) {
    define('JDEBUG', JFactory::getConfig()->get('debug'));
}

/**
 *  Koowa Component JFactory stub
 *
 * This class acts as a stub for JFactory. Calls are intercepted and return NULL. Only calls for JFactory::getConfig()->get()
 * will return a corresponding value.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa
 */
class ComKoowa
{
    private static $__object;

    public static $application = null;
    public static $language    = null;
    public static $config      = null;

    public static function getApplication()
    {
        if (!self::$application)
        {
            $application = new class
            {
                public $input;

                public function __construct()
                {
                    $this->input = new class() {
                        public function __call($method, $parameters) {}
                    };
                }

                public function __call($method, $parameters) {}

            };

            self::$application = $application;
        }

        return self::$application;
    }

    public static function getLanguage()
    {
        if (!self::$language)
        {
            $language = new class
            {
                public $strings;

                public function __construct(){
                    $this->strings = array();
                }

                public function __call($method, $parameters) {}
            };

            self::$language = $language;
        }

        return self::$language;
    }

    public static function getConfig()
    {
        if (!static::$config)
        {
            $config = new class
            {
                private $__config;

                public function __construct(){

                    if(file_exists(JPATH_CONFIGURATION.'/koowa.php')) {
                        $this->__config = require_once JPATH_CONFIGURATION.'/koowa.php';
                    } else {
                        $this->__config = array();
                    }
                }

                public function get($name)
                {
                    $result = null;
                    if(isset($this->__config[$name])) {
                        $result = $this->__config[$name];
                    }

                    return $result;
                }

                public function __get($name) {
                    return $this->get($name);
                }
            };

            self::$config= $config;
        }

        return static::$config;
    }

    public static function __callStatic($method, $parameters)
    {
        if(!isset(static::$__object))
        {
            static::$__object = new class {
                public function __get($name) { }
                public function __call($method, $parameters) {}
            };
        }

        return static::$__object;
    }
}