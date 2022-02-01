<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Folder Database Row
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityFolder extends ComFilesModelEntityNode implements KCommandCallbackDelegate
{
	/**
	 * Nodes object or identifier
	 *
	 * @var string|object
	 */
	protected $_children = null;

	/**
	 * Node object or identifier
	 *
	 * @var string|object
	 */
	protected $_parent   = null;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        if(isset($config->parent)) {
            $this->setParent($config->parent);
        }

        foreach($config->children as $child) {
            $this->insertChild($child);
        }
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'children'  => array(),
            'parent'	=> null,
        ));

        parent::_initialize($config);
    }

	/**
	 * Invoke a command handler
	 *
	 * @param string             $method    The name of the method to be executed
	 * @param KCommandInterface  $command   The command
	 * @return mixed Return the result of the handler.
	 */
	public function invokeCommandCallback($method, KCommandInterface $command)
	{
		return $this->$method($command);
	}

	/**
	 * Stores the parent contents before creating a folder
	 *
	 * @param KDatabaseContextInterface $context
	 */
	protected function _beforeSave(KDatabaseContextInterface $context)
	{
		if (!$context->siblings) {
			$context->siblings = array();
		}

		$parent = dirname($context->getSubject()->fullpath);

		if (file_exists($parent)) {
            $context->siblings[] = scandir(dirname($context->getSubject()->fullpath));
        }
	}

	/**
	 * Sets the folder name as created by the OS (encoding) in the filesystem
	 *
	 * @param KDatabaseContextInterface $context
	 */
	protected function _afterSave(KDatabaseContextInterface $context)
	{
		if ($context->siblings && count($context->siblings))
		{
			$siblings = KObjectConfig::unbox($context->siblings);

			$name = array_diff(scandir(dirname($context->getSubject()->fullpath)), array_pop($siblings));

			if (count($name) == 1) {
				$this->name = current($name);
			}

			$context->siblings = $siblings;
		}
	}

	public function save()
	{
		$context = $this->getContext();
		$context->result = false;

		$is_new = $this->isNew();

		if ($this->invokeCommand('before.save', $context) !== false)
		{
			if ($this->isNew()) {
				$context->result = $this->_adapter->create();
			}

			$this->invokeCommand('after.save', $context);
		}

		if ($context->result === false) {
			$this->setStatus(KDatabase::STATUS_FAILED);
		}
		else $this->setStatus($is_new ? KDatabase::STATUS_CREATED : KDatabase::STATUS_UPDATED);

		return $context->result;
	}

	public function getProperties($modified = false)
	{
		$result = parent::getProperties($modified);

		if (isset($result['children']) && $result['children'] instanceof KModelEntityInterface) {
			$result['children'] = $result['children']->getProperties();
		}

		return $result;
	}

	public function insertChild(KModelEntityInterface $node)
	{
		//Track the parent
		$node->setParent($this);

		$this->getChildren()->insert($node);

		return $this;
	}

	public function hasChildren()
	{
		return is_null($this->_children) ? false : (boolean) count($this->_children);
	}

	/**
	 * Get the children entity
	 *
	 * @return	object
	 */
	public function getChildren()
	{
		if(!($this->_children instanceof KModelEntityInterface))
		{
			$identifier         = $this->getIdentifier()->toArray();
			$identifier['path'] = array('model', 'entity');
			$identifier['name'] = KStringInflector::pluralize($this->getIdentifier()->name);

			//The row default options
			$options  = array(
                'identity_key' => $this->getIdentityKey()
			);

			$this->_children = $this->getObject($identifier, $options);
		}

		return $this->_children;
	}

	/**
	 * Get the parent node
	 *
	 * @return	ComFilesModelEntityFolder
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Set the parent node
	 *
     * @param  ComFilesModelEntityFolder $node
	 * @return $this
	 */
	public function setParent($node)
	{
		$this->_parent = $node;

		return $this;
	}

    public function toArray()
    {
        $data = parent::toArray();

        if ($this->hasChildren()) {
            $data['children'] = array_values($this->getChildren()->toArray());
        }

        return $data;
    }
}
