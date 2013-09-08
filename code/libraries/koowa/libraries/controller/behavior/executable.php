<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2007 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa for the canonical source repository
 */

/**
 * Executable Controller Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Controller
 */
class KControllerBehaviorExecutable extends KControllerBehaviorAbstract
{
	/**
	 * The read-only state of the behavior
	 *
	 * @var boolean
	 */
	protected $_readonly;

	/**
	 * Constructor.
	 *
	 * @param   KObjectConfig $config Configuration options
	 */
	public function __construct( KObjectConfig $config)
	{
		parent::__construct($config);

		$this->_readonly = (bool) $config->readonly;
	}

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'   => KCommand::PRIORITY_HIGH,
            'readonly'   => false,
            'auto_mixin' => true
        ));

        parent::_initialize($config);
    }

	/**
     * Command handler
     *
     * Only handles before.action commands to check ACL rules.
     *
     * @param   string          $name       The command name
     * @param   KCommandContext $context    The command context
     * @throws  KControllerExceptionForbidden
     * @throws  KControllerExceptionNotImplemented
     * @return  boolean
     */
    public function execute( $name, KCommandContext $context)
    {
        $parts = explode('.', $name);

        if($parts[0] == 'before')
        {
            $action = $parts[1];

            if($this->canExecute($action) === false)
            {
                if(!JFactory::getUser()->guest) {
                    $context->setError(new KControllerExceptionForbidden('Action '.ucfirst($action).' Not Allowed'));
                } else {
                    $context->setError(new KControllerExceptionUnauthorized('Action '.ucfirst($action).' Not Allowed'));
                }

                return false;
            }
        }

        return true;
    }

 	/**
     * Get an object handle
     *
     * Force the object to be enqueue in the command chain.
     *
     * @return string A string that is unique, or NULL
     * @see execute()
     */
    public function getHandle()
    {
        return KObjectMixinAbstract::getHandle();
    }

    /**
     * Set the readonly state of the behavior
     *
     * @param boolean
     * @return KControllerBehaviorExecutable
     */
    public function setReadOnly($readonly)
    {
         $this->_readonly = (bool) $readonly;
         return $this;
    }

    /**
     * Get the readonly state of the behavior
     *
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->_readonly;
    }

    /**
     * Check if an action can be executed
     *
     * @param   string  $action Action name
     * @return  boolean True if the action can be executed, otherwise FALSE.
     */
    public function canExecute($action)
    {
        //Check if the action is allowed
        $method = 'can'.ucfirst($action);

        if(!in_array($method, $this->getMixer()->getMethods()))
        {
            $actions = $this->getActions();
            $actions = array_flip($actions);

            $result = isset($actions[$action]);
        }
        else $result = $this->$method();

        return $result;
    }

	/**
     * Generic authorize handler for controller browse actions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canBrowse()
    {
        return true;
    }

	/**
     * Generic authorize handler for controller read actions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canRead()
    {
        return true;
    }

	/**
     * Generic authorize handler for controller edit actions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canEdit()
    {
        return !$this->_readonly;
    }

 	/**
     * Generic authorize handler for controller add actions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canAdd()
    {
        return !$this->_readonly;
    }

 	/**
     * Generic authorize handler for controller delete actions
     *
     * @return  boolean     Can return both true or false.
     */
    public function canDelete()
    {
         return !$this->_readonly;
    }
}
