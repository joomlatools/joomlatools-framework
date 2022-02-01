<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Cacheable Controller Behavior
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesControllerBehaviorCacheable extends ComKoowaControllerBehaviorCacheable
{
    /**
     * Cache object
     *
     * @var JCache
     */
    protected $_cache;

    /**
     * Cache group
     *
     * @var string
     */
    protected $_group = '';

    /**
     * If true, behavior only clears the cache after add/edit/delets and do not store new data
     *
     * @var boolean
     */
    protected $_only_clear = false;

    /**
     * Constructor
     *
     * @param KObjectConfig $config An optional KObjectConfig object with configuration options.
     */
    public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);
		
		$this->_group      = $config->group;
        $this->_only_clear = $config->only_clear;
	}

    /**
     * @param KObjectConfig $config
     */
    protected function _initialize(KObjectConfig $config)
	{
		$config->append(array(
			'priority'   => self::PRIORITY_LOWEST,
			'group'      => 'com_files',
            'only_clear' => false // If true, behavior only clears the cache after add/edit/delete and does not store new data
		));

		parent::_initialize($config);
	}

    /**
     * Create a JCache instance
     *
     * @param string $group
     * @param string $handler
     * @param null   $storage
     * @return JCache
     */
    protected function _getCache($group = '', $handler = 'callback', $storage = null)
	{
		if (!$this->_cache) 
		{
			jimport('joomla.cache.cache');

            $app     = JFactory::getApplication();
			$options = array(
				'caching' 		=> true, 
				'defaultgroup'  => $this->_getGroup(),
				'lifetime' 		=> 60*24*180,
				'cachebase' 	=> JPATH_ADMINISTRATOR.'/cache',
				'language' 		=> $app->getCfg('language'),
				'storage'		=> $app->getCfg('cache_handler', 'file')
			);
			
			$this->_cache = JCache::getInstance('output', $options);
		}
		
		return $this->_cache;
	}

    /**
     * Clears group cache
     *
     * @return boolean
     */
    protected function _cleanCache()
    {
        return $this->_getCache()->clean($this->_getGroup());
    }

    /**
     * Set the event output from the cache
     *
     * @param KControllerContextInterface $context
     * @return boolean
     */
    protected function _setOutput(KControllerContextInterface $context)
	{
		$cache  = $this->_getCache();
		$key    = $this->_getKey();
        $data   = $cache->get($key);

		if ($data)
		{
			$data = unserialize($data);
	
			$context->result = $data['component'];
            $this->_output   = $context->result;

            $context->response->setContent($context->result, $this->getView()->mimetype);

            return false;
		}

        return true;
	}
	
	/**
	 * Store the unrendered view data in the cache
	 *
	 * @param   KControllerContextInterface $context	A command context object
	 * @return 	void
	 */
	protected function _storeOutput(KControllerContextInterface $context)
	{
		if (empty($this->_output))
		{
			$cache  = $this->_getCache();
			$key    = $this->_getKey();
			
			$data  = array();
			$data['component'] = $context->result;

			$cache->store(serialize($data), $key);
		}
	}

    /**
     * Sets the output for JSON requests from cache if possible
     *
     * Also cleans cache if revalidate_cache property is set in request
     *
     * @param KControllerContextInterface $context
     * @return boolean
     */
    protected function _beforeRender(KControllerContextInterface $context)
	{
		if ($this->getRequest()->isSafe() && $this->getRequest()->getFormat() === 'json' && $this->_only_clear === false)
        {
            if ($this->getRequest()->query->revalidate_cache) {
                $this->_cleanCache();
            } else {
                return $this->_setOutput($context);
            }
		}

        return true;
	}

    /**
     * Stores the cache output for JSON requests
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterRender(KControllerContextInterface $context)
	{
		if ($this->getRequest()->isSafe() && $this->getRequest()->getFormat() === 'json' && $this->_only_clear === false) {
			$this->_storeOutput($context);
		}
	}

    /**
     * Overridden to not run caching on read actions
     *
     * @param KControllerContextInterface $context
     */
    protected function _beforeRead(KControllerContextInterface $context)
	{
	}

    /**
     * Overridden to not run caching on read actions
     *
     * @param KControllerContextInterface $context
     */
	protected function _afterRead(KControllerContextInterface $context)
	{
	}

    /**
     * Clean the cache
     *
     * @param   KControllerContextInterface $context A command context object
     * @return  boolean
     */
    protected function _afterCopy(KControllerContextInterface $context)
    {
        $status = $context->result->getStatus();

        if($status !== KDatabase::STATUS_FAILED) {
            $this->_getCache()->clean($this->_getGroup());
        }

        return true;
    }

    /**
     * Clean the cache
     *
     * @param   KControllerContextInterface $context A command context object
     * @return  boolean
     */
    protected function _afterMove(KControllerContextInterface $context)
    {
        $status = $context->result->getStatus();

        if($status !== KDatabase::STATUS_FAILED) {
            $this->_getCache()->clean($this->_getGroup());
        }

        return true;
    }

    /**
     * Returns cache group name
     *
     * @return string
     */
    protected function _getGroup()
	{
		return $this->_group;
	}

    /**
     * Returns cache key
     *
     * Converts empty strings to null in state data before creating the key
     *
     * Keys are formatted as: folder:md5(state)
     *
     * @return string
     */
    protected function _getKey()
	{
	    $state = $this->getModel()->getState()->getValues();
	    
	    // Empty strings get sent in the URL for dispatched requests 
	    // so we turn them to null before creating the key
	    foreach ($state as $key => $value)
        {
			if ($value === '') {
				$state[$key] = null;
			}
	    }

        unset($state['config']);

	    $key = $this->getModel()->getState()->folder.':'.md5(http_build_query($state, '', '&'));

	    return $key;
	}
}
