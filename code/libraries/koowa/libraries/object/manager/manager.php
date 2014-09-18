<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Object manager
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Object\Manager
 */
final class KObjectManager implements KObjectInterface, KObjectManagerInterface, KObjectSingleton
{
    /**
     * The object identifier
     *
     * @var KObjectIdentifier
     */
    private $__object_identifier;

    /**
     * The object registry
     *
     * @var KObjectRegistry
     */
    private $__registry;

    /*
    * The class loader
    *
    * @var KClassLoader
    */
    private $__loader;

    /**
     * The identifier locators
     *
     * @var array
     */
    protected $_locators = array();

    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the constructor private
     */
    final private function __construct(KObjectConfig $config)
    {
        //Initialise the object
        $this->_initialize($config);

        // Set the class loader
        if (!$config->class_loader instanceof KClassLoaderInterface)
        {
            throw new InvalidArgumentException(
                'class_loader [KClassLoaderInterface] config option is required, "'.gettype($config->class_loader).'" given.'
            );
        }

        //Set the class loader
        $this->setClassLoader($config->class_loader);

        //Create the object registry
        if($config->cache && KObjectRegistryCache::isSupported())
        {
            $this->__registry = new KObjectRegistryCache();
            $this->__registry->setNamespace($config->cache_namespace);
        }
        else $this->__registry = new KObjectRegistry();

        //Create the object identifier
        $this->__object_identifier = $this->getIdentifier('object.manager');

        //Manually register the koowa loader
        $config = new KObjectConfig(array(
            'object_manager'    => $this,
            'object_identifier' => new KObjectIdentifier('lib:object.locator.koowa')
        ));

        $this->registerLocator(new KObjectLocatorLibrary($config));

        //Register self and set a 'manager' alias
        $this->setObject('object.manager', $this);
        $this->registerAlias('object.manager', 'manager');
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config An optional KObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'class_loader'    => null,
            'cache'           => false,
            'cache_namespace' => 'koowa'
        ));
    }

    /**
     * Prevent creating clones of this class
     *
     * @throws Exception
     */
    final private function __clone()
    {
        trigger_error("The object manager cannot be cloned.", E_USER_WARNING);
    }

    /**
     * Force creation of a singleton
     *
     * @param  array  $config An optional array with configuration options.
     * @return KObjectManager
     */
    final public static function getInstance($config = array())
    {
        static $instance;

        if ($instance === NULL)
        {
            if(!$config instanceof KObjectConfig) {
                $config = new KObjectConfig($config);
            }

            $instance = new self($config);
        }

        return $instance;
    }

    /**
     * Get an object instance based on an object identifier
     *
     * If the object implements the ObjectInstantiable interface the manager will delegate object instantiation
     * to the object itself.
     *
     * @param   mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @param   array $config     An optional associative array of configuration settings
     * @return  KObjectInterface  Return object on success, throws exception on failure
     * @throws  KObjectExceptionInvalidIdentifier If the identifier is not valid
     * @throws  KObjectExceptionInvalidObject     If the object doesn't implement the KObjectInterface
     * @throws  KObjectExceptionNotFound          If object cannot be loaded
     * @throws  KObjectExceptionNotInstantiated   If object cannot be instantiated
     */
    public function getObject($identifier, array $config = array())
    {
        $identifier = $this->getIdentifier($identifier);

        if (!$instance = $this->isRegistered($identifier))
        {
            //Instantiate the identifier
            $instance = $this->_instantiate($identifier, $config);

            //Mixins's are early mixed in KObject::_construct()
            //$instance = $this->_mixin($identifier, $instance);

            //Decorate the object
            $instance = $this->_decorate($identifier, $instance);

            //Auto register the object
            if($this->isMultiton($identifier)) {
                $this->setObject($identifier, $instance);
            }
        }

        return $instance;
    }

    /**
     * Insert the object instance using the identifier
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @param object $object    The object instance to store
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     * @return void
     */
    public function setObject($identifier, $object)
    {
        $identifier = $this->getIdentifier($identifier);

        //Add alias for singletons
        if($identifier->getType() != 'lib' && $this->isSingleton($identifier))
        {
            $parts = $identifier->toArray();

            unset($parts['type']);
            unset($parts['domain']);
            unset($parts['package']);

            //Create singleton identifier : path.name
            $singleton = $this->getIdentifier($parts);

            $this->registerAlias($identifier, $singleton);
        }

        $this->__registry->set($identifier, $object);
    }

    /**
     * Returns an identifier object.
     *
     * Accepts various types of parameters and returns a valid identifier. Parameters can either be an
     * object that implements KObjectInterface, or a KObjectIdentifier object, or valid identifier
     * string. Function recursively resolves identifier aliases and returns the aliased identifier.
     *
     * If the identifier does not have a type set default type to 'lib'. Eg, event.publisher is the same as
     * lib:event.publisher.
     *
     * If no identifier is passed the object identifier of this object will be returned.
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @return KObjectIdentifier
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     */
    public function getIdentifier($identifier = null)
    {
        //Get the identifier
        if(isset($identifier))
        {
            if(!$identifier instanceof KObjectIdentifierInterface)
            {
                if ($identifier instanceof KObjectInterface) {
                    $identifier = $identifier->getIdentifier();
                } else {
                    $identifier = new KObjectIdentifier($identifier);
                }
            }

            //Get the identifier object
            if (!$result = $this->__registry->find($identifier)) {
                $result = $this->__registry->set($identifier);
            }
        }
        else $result = $this->__object_identifier;

        return $result;
    }

    /**
     * Set an identifier
     *
     * This function will reset the identifier if it has already been set. Use this very carefully as it can have
     * unwanted side-effects.
     *
     * @param KObjectIdentifier  $identifier An ObjectIdentifier
     * @return KObjectManager
     */
    public function setIdentifier(KObjectIdentifier $identifier)
    {
        $this->__registry->set($identifier);
        return $this;
    }

    /**
     * Check if an identifier exists
     *
     * @param mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @return bool TRUE if the identifier exists, false otherwise.
     */
    public function hasIdentifier($identifier)
    {
        return $this->__registry->has($identifier);
    }

    /**
     * Get the identifier class
     *
     * @param mixed $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @param bool  $fallback   Use fallbacks when locating the class. Default is TRUE.
     * @return string|false  Returns the class name or false if the class could not be found.
     */
    public function getClass($identifier, $fallback = true)
    {
        $identifier = $this->getIdentifier($identifier);
        $class      = $this->__registry->getClass($identifier);

        //If the class is FALSE we have tried to locate it already, do not locate it again.
        if(empty($class) && $class !== false)
        {
            $class = $this->_locate($identifier, $fallback);

            //If we are falling back set the class in the registry
            if($fallback)
            {
                if(!$this->__registry->get($identifier) instanceof KObjectInterface) {
                    $this->__registry->setClass($identifier, $class);
                }
            }
        }

        return $class;
    }

    /**
     * Get the object configuration
     *
     * @param mixed  $identifier An ObjectIdentifier, identifier string or object implementing ObjectInterface
     * @return KObjectConfig
     * @throws KObjectExceptionInvalidIdentifier  If the identifier is not valid
     */
    public function getConfig($identifier = null)
    {
        $config = $this->getIdentifier($identifier)->getConfig();
        return $config;
    }

    /**
     * Register a mixin for an identifier
     *
     * The mixin is mixed when the identified object is first instantiated see {@link get} The mixin is also mixed with
     * with the represented by the identifier if the object is registered in the object manager. This mostly applies to
     * singletons but can also apply to other objects that are manually registered.
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @param mixed $mixin      An KObjectIdentifier, identifier string or object implementing KObjectMixinInterface
     * @param array $config     Configuration for the mixin
     * @return KObjectManager
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     * @see KObjectMixable::mixin()
     */
    public function registerMixin($identifier, $mixin, $config = array())
    {
        $identifier = $this->getIdentifier($identifier);

        if ($mixin instanceof KObjectMixinInterface || $mixin instanceof KObjectIdentifier) {
            $identifier->getMixins()->append(array($mixin));
        } else {
            $identifier->getMixins()->append(array($mixin => $config));
        }

        //If the identifier already exists mixin the mixin
        if ($this->isRegistered($identifier))
        {
            $mixer = $this->__registry->get($identifier);
            $this->_mixin($identifier, $mixer);
        }

        return $this;
    }

    /**
     * Register a decorator  for an identifier
     *
     * The object is decorated when it's first instantiated see {@link get} The object represented by the identifier is
     * also decorated if the object is registered in the object manager. This mostly applies to singletons but can also
     * apply to other objects that are manually registered.
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @param mixed $decorator  An KObjectIdentifier, identifier string or object implementing KObjectDecoratorInterface
     * @param array $config     Configuration for the decorator
     * @return KObjectManager
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     * @see KObjectDecoratable::decorate()
     */
    public function registerDecorator($identifier, $decorator, $config = array())
    {
        $identifier = $this->getIdentifier($identifier);

        if ($decorator instanceof KObjectDecoratorInterface || $decorator instanceof KObjectIdentifier) {
            $identifier->getDecorators()->append(array($decorator));
        } else {
            $identifier->getDecorators()->append(array($decorator => $config));
        }

        //If the identifier already exists decorate it
        if ($this->isRegistered($identifier))
        {
            $delegate = $this->__registry->get($identifier);
            $this->_decorate($identifier, $delegate);
        }

        return $this;
    }

    /**
     * Register an object locator
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectLocatorInterface
     * @param array $config
     * @throws UnexpectedValueException
     * @return KObjectManager
     */
    public function registerLocator($identifier, array $config = array())
    {
        if(!$identifier instanceof KObjectLocatorInterface)
        {
            $locator = $this->getObject($identifier, $config);

            if(!$locator instanceof KObjectLocatorInterface)
            {
                throw new UnexpectedValueException(
                    'Locator: '.get_class($locator).' does not implement KObjectLocatorInterface'
                );
            }
        }
        else $locator = $identifier;

        //Add the locator
        $this->_locators[$locator->getName()] = $locator;

        return $this;
    }

    /**
     * Get a registered object locator based on his type
     *
     * @param string $type The locator type
     * @return KObjectLocatorInterface|null  Returns the object locator or NULL if it cannot be found.
     */
    public function getLocator($type)
    {
        $result = null;

        if(isset($this->_locators[$type])) {
            $result = $this->_locators[$type];
        }

        return $result;
    }

    /**
     * Get the registered class locators
     *
     * @return array
     */
    public function getLocators()
    {
        return $this->_locators;
    }

    /**
     * Set an alias for an identifier
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @param string $alias     The identifier alias
     * @return KObjectManager
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     */
    public function registerAlias($identifier, $alias)
    {
        $identifier = $this->getIdentifier($identifier);
        $alias      = $this->getIdentifier($alias);

        //Register the alias for the identifier
        $this->__registry->alias($identifier, (string) $alias);

        //Merge alias configuration into the identifier
        $identifier->getConfig()->append($alias->getConfig());

        // Register alias mixins.
        foreach ($alias->getMixins() as $mixin) {
            $this->registerMixin($identifier, $mixin);
        }

        return $this;
    }

    /**
     * Get the aliases for an identifier
     *
     * @param mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @return array   An array of aliases
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     */
    public function getAliases($identifier)
    {
        return array_search((string) $identifier, $this->__registry->getAliases());
    }

    /**
     * Get the class loader
     *
     * @return KClassLoaderInterface
     */
    public function getClassLoader()
    {
        return $this->__loader;
    }

    /**
     * Set the class loader
     *
     * @param  KClassLoaderInterface $loader
     * @return KObjectManagerInterface
     */
    public function setClassLoader(KClassLoaderInterface $loader)
    {
        $this->__loader = $loader;
        return $this;
    }

    /**
     * Check if the object instance exists based on the identifier
     *
     * @param  mixed $identifier An KObjectIdentifier, identifier string or object implementing KObjectInterface
     * @return KObjectInterface|false Returns the registered object on success or FALSE on failure.
     * @throws KObjectExceptionInvalidIdentifier If the identifier is not valid
     */
    public function isRegistered($identifier)
    {
        $result = false;

        try
        {
            $registered = $this->getIdentifier($identifier);

            //Get alias for singletons
            if($registered->getType() != 'lib' && $this->isSingleton($registered))
            {
                $parts = $registered->toArray();

                unset($parts['type']);
                unset($parts['domain']);
                unset($parts['package']);

                //Create singleton identifier : path.name
                $registered = $this->getIdentifier($parts);
            }

            $object = $this->__registry->get($registered);

            //If the object implements ObjectInterface we have registered an object
            if($object instanceof KObjectInterface) {
                $result = $object;
            }

        } catch (KObjectExceptionInvalidIdentifier $e) {}

        return $result;
    }

    /**
     * Check if the object is a multiton
     *
     * @param mixed $identifier An object that implements the ObjectInterface, an ObjectIdentifier or valid identifier string
     * @return boolean Returns TRUE if the object is a singleton, FALSE otherwise.
     */
    public function isMultiton($identifier)
    {
        $result = false;
        $class  = $this->getClass($identifier);

        if($class) {
            $result = array_key_exists('KObjectMultiton', class_implements($class));
        }

        return $result;

    }

    /**
     * Check if the object is a singleton
     *
     * @param mixed $identifier An object that implements the ObjectInterface, an ObjectIdentifier or valid identifier string
     * @return boolean Returns TRUE if the object is a singleton, FALSE otherwise.
     */
    public function isSingleton($identifier)
    {
        $result = false;
        $class  = $this->getClass($identifier);

        if($class) {
            $result = array_key_exists('KObjectSingleton', class_implements($class));
        }

        return $result;
    }

    /**
     * Perform the actual mixin of all registered mixins for an object
     *
     * @param  KObjectIdentifier $identifier
     * @param  KObjectMixable    $mixer
     * @return KObjectMixable    The mixed object
     */
    protected function _mixin(KObjectIdentifier $identifier, $mixer)
    {
        if ($mixer instanceof KObjectMixable)
        {
            $mixins = $identifier->getMixins();

            foreach ($mixins as $key => $value)
            {
                if (is_numeric($key)) {
                    $mixer->mixin($value);
                } else {
                    $mixer->mixin($key, $value);
                }
            }
        }

        return $mixer;
    }

    /**
     * Perform the actual decoration of all registered decorators for an object
     *
     * @param  KObjectIdentifier  $identifier
     * @param  KObjectDecoratable $delegate
     * @return KObjectDecorator  The decorated object
     */
    protected function _decorate(KObjectIdentifier $identifier, $delegate)
    {
        if ($delegate instanceof KObjectDecoratable)
        {
            $decorators = $identifier->getDecorators();

            foreach ($decorators as $key => $value)
            {
                if (is_numeric($key)) {
                    $delegate = $delegate->decorate($value);
                } else {
                    $delegate = $delegate->decorate($key, $value);
                }
            }
        }

        return $delegate;
    }

    /**
     * Configure an identifier
     *
     * @param KObjectIdentifier $identifier
     * @param array             $data
     * @return KObjectConfig
     */
    protected function _configure(KObjectIdentifier $identifier, array $data = array())
    {
        //Prevent config settings from being stored in the identifier
        $config = clone $identifier->getConfig();

        //Merge the config data
        $config->append($data);

        //Set the service container and identifier
        $config->object_manager    = $this;
        $config->object_identifier = $identifier;

        return $config;
    }

    /**
     * Get an instance of a class based on a class identifier
     *
     * @param KObjectIdentifier $identifier
     * @param bool              $fallback   Use fallbacks when locating the class. Default is TRUE.
     * @return  string  Return the identifier class or FALSE on failure.
     */
    protected function _locate(KObjectIdentifier $identifier, $fallback = true)
    {
        //Set loader basepath if we are locating inside an application
        if($this->isRegistered('object.bootstrapper'))
        {
             if($path = $this->getObject('object.bootstrapper')->getApplicationPath($identifier->domain)) {
                 $this->getClassLoader()->setBasePath($path);
             }
        }

        return $this->_locators[$identifier->getType()]->locate($identifier, $fallback);
    }

    /**
     * Get an instance of a class based on a class identifier
     *
     * @param   KObjectIdentifier $identifier
     * @param   array              $config      An optional associative array of configuration settings.
     * @return  object  Return object on success, throws exception on failure
     * @throws	KObjectExceptionInvalidObject     If the object doesn't implement the KObjectInterface
     * @throws  KObjectExceptionNotFound          If object cannot be loaded
     * @throws  KObjectExceptionNotInstantiated   If object cannot be instantiated
     */
    protected function _instantiate(KObjectIdentifier $identifier, array $config = array())
    {
        $result = null;

        //Get the class name and set it in the identifier
        $class = $this->getClass($identifier);

        if($class && class_exists($class))
        {
            if (!array_key_exists('KObjectInterface', class_implements($class, false)))
            {
                throw new KObjectExceptionInvalidObject(
                    'Object: '.$class.' does not implement KObjectInterface'
                );
            }

            //Configure the identifier
            $config = $this->_configure($identifier, $config);

            // Delegate object instantiation.
            if (array_key_exists('KObjectInstantiable', class_implements($class, false))) {
                $result = call_user_func(array($class, 'getInstance'), $config, $this);
            } else {
                $result = new $class($config);
            }

            //Thrown an error if no object was instantiated
            if (!is_object($result))
            {
                throw new KObjectExceptionNotInstantiated(
                    'Cannot instantiate object from identifier: ' . $class
                );
            }
        }
        else throw new KObjectExceptionNotFound('Cannot load object from identifier: '. $identifier);

        return $result;
    }
}
