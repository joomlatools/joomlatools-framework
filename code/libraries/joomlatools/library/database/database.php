<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Database
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Database
 */
class KDatabase extends KObject implements KObjectSingleton
{
    /**
     * Database result mode
     */
    const RESULT_STORE = 0;
    const RESULT_USE   = 1;

    /**
     * Database fetch mode
     */
    const FETCH_ARRAY       = 0;
    const FETCH_ARRAY_LIST  = 1;
    const FETCH_FIELD       = 2;
    const FETCH_FIELD_LIST  = 3;
    const FETCH_OBJECT      = 4;
    const FETCH_OBJECT_LIST = 5;

    const FETCH_ROW         = 6;
    const FETCH_ROWSET      = 7;

    /**
     * Row states
     */
    const STATUS_FETCHED  = 'fetched';
    const STATUS_DELETED  = 'deleted';
    const STATUS_CREATED  = 'created';
    const STATUS_UPDATED  = 'updated';
    const STATUS_FAILED   = 'failed';

    /**
     * @var KDatabaseAdapterInterface
     */
    protected $_adapter;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        if ($config->adapter) {
            $this->setAdapter($config->adapter);
        }
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append([
            'adapter' => 'database.adapter.mysqli'
        ]);

        parent::_initialize($config);
    }

    /**
     * Get the main database adapter
     *
     * @return KDatabaseAdapterInterface
     */
    public function getAdapter()
    {
        if(!$this->_adapter instanceof KDatabaseAdapterInterface)
        {
            //Make sure we have a model identifier
            if(!($this->_adapter instanceof KObjectIdentifier)) {
                $this->setAdapter($this->_adapter);
            }

            $this->_adapter = $this->getObject($this->_adapter);

            if(!$this->_adapter instanceof KDatabaseAdapterInterface)
            {
                throw new UnexpectedValueException(
                    'Adapter: '.get_class($this->_adapter).' does not implement KModelInterface'
                );
            }
        }

        return $this->_adapter;
    }

    /**
     * @param $adapter
     * @return mixed
     */
    public function setAdapter($adapter)
    {
        if(!($adapter instanceof KModelInterface))
        {
            if(is_string($adapter) && !str_contains($adapter, '.'))
            {
                $identifier         = $this->getIdentifier()->toArray();
                $identifier['path'] = isset($identifier['package']) && $identifier['package'] === 'database' ? ['adapter'] : ['database', 'adapter'];
                $identifier['name'] = $adapter;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($adapter);

            $adapter = $identifier;
        }

        $this->_adapter = $adapter;

        return $this->_adapter;
    }

    /**
     * Returns a query object with the current adapter set
     *
     * @param string|KObjectIdentifier $identifier Query type (e.g. `select`, `insert`) or a full identifier
     * @return KDatabaseQueryInterface
     */
    public function getQuery($identifier)
    {
        return $this->getAdapter()->getQuery($identifier);
    }
}
