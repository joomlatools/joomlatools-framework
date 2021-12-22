<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Object Config Factory
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object\Config
 */
final class KObjectConfigFactory extends KObject implements KObjectSingleton
{
    /**
     * Config object prototypes
     *
     * @var array
     */
    private $__prototypes;

    /**
     * Registered config file formats.
     *
     * @var array
     */
    protected $_formats;

    /**
     * Constructor
     *
     * @param KObjectConfig $config An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_formats = array();

        foreach($config->formats as $format => $identifier) {
            $this->registerFormat($format, $identifier);
        }
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config	An optional KObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'formats' => array(
                'php'  => 'lib:object.config.php',
                'ini'  => 'lib:object.config.ini',
                'json' => 'lib:object.config.json',
                'yml'  => 'lib:object.config.yaml',
                'yaml' => 'lib:object.config.yaml'
            )
        ));

        parent::_initialize($config);
    }

    /**
     * Get a registered config object.
     *
     * @param  string $format The format name
     * @param  array|KObjectConfig $options An associative array of configuration options or a KObjectConfig instance.
     * @throws InvalidArgumentException    If the format isn't registered
     * @throws \UnexpectedValueException   If the format object doesn't implement the KObjectConfigSerializable
     * @return KObjectConfigSerializable
     */
    public function createFormat($format, $options = array())
    {
        $name = strtolower($format);

        if (!isset($this->_formats[$name])) {
            throw new RuntimeException(sprintf('Unsupported config format: %s ', $name));
        }

        if(!isset($this->__prototypes[$name]))
        {
            $class    = $this->_formats[$name];
            $instance = new $class();

            if(!$instance instanceof KObjectConfigSerializable)
            {
                throw new UnexpectedValueException(
                    'Format: '.get_class($instance).' does not implement ObjectConfigSerializable Interface'
                );
            }

            $this->__prototypes[$name] = $instance;
        }

        //Clone the object
        $result = clone $this->__prototypes[$name];
        $result->merge($options);

        return $result;
    }

    /**
     * Register config format
     *
     * @param string $format The name of the format
     * @param string  $identifier A fully qualified object identifier
     * @throws InvalidArgumentException If the class does not exist
     * @return KObjectConfigFactory
     */
    public function registerFormat($format, $identifier)
    {
        $class = $this->getObject('manager')->getClass($identifier);

        if(!class_exists($class, true)) {
            throw new InvalidArgumentException('Class : '.$class.' cannot does not exist.');
        }

        $this->_formats[$format] = $class;

        //In case the format is being re-registered clear the prototype
        if(isset($this->__prototypes[$format])) {
            unset($this->__prototypes[$format]);
        }

        return $this;
    }

    /**
     * Read a config from a string
     *
     * @param  string  $format
     * @param  string  $config
     * @param  bool    $object  If TRUE return a ConfigObject, if FALSE return an array. Default TRUE.
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return KObjectConfigInterface|array
     */
    public function fromString($format, $config, $object = true)
    {
        $config = $this->createFormat($format)->fromString($config, $object);
        return $config;
    }

    /**
     * Read a config from a file.
     *
     * @param  string  $filename
     * @param  bool    $object  If TRUE return a ConfigObject, if FALSE return an array. Default TRUE.
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @return KObjectConfigInterface|array
     */
    public function fromFile($filename, $object = true)
    {
        $pathinfo = pathinfo($filename);

        if (!isset($pathinfo['extension']))
        {
            throw new RuntimeException(sprintf(
                'Filename "%s" is missing an extension and cannot be auto-detected', $filename
            ));
        }

        $config = $this->createFormat($pathinfo['extension'])->fromFile($filename, $object);
        return $config;
    }

    /**
     * Writes a config to a file
     *
     * @param string $filename
     * @param KObjectConfigInterface|array $config
     * @throws RuntimeException
     * @return KObjectConfigFactory
     */
    public function toFile($filename, $config)
    {
        $pathinfo = pathinfo($filename);

        if (!isset($pathinfo['extension']))
        {
            throw new RuntimeException(sprintf(
                'Filename "%s" is missing an extension and cannot be auto-detected', $filename
            ));
        }

        $this->createFormat($pathinfo['extension'], $config)->toFile($filename);
        return $this;
    }

    /**
     * Check if the format is registered
     *
     * @param string $format A config format
     * @return bool TRUE if the format is a registered, FALSE otherwise.
     */
    public function isRegistered($format)
    {
        return isset($this->_formats[$format]);
    }
}