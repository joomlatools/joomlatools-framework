<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Searchable Model Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Model\Behavior
 */
class KModelBehaviorSearchable extends KModelBehaviorAbstract
{
    /**
     * The column names to search in
     *
     * Default is 'title'.
     *
     * @var array
     */
    protected $_columns = [];

    protected $_alias_map = [];

    /**
     * Constructor.
     *
     * @param   KObjectConfig $config An optional KObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $columns = (array) KObjectConfig::unbox($config->columns);

        foreach ($columns as $column)
        {
            $parts = explode('.', $column);

            if (count($parts) >= 2)
            {
                $alias = array_shift($parts);
                $column = implode(',', $parts);

                $this->_alias_map[$column] = $alias;
            }
            
            $this->_columns[] = $column;
        }

        $this->addCommandCallback('before.fetch', '_buildQuery')
            ->addCommandCallback('before.count', '_buildQuery');
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config An optional KObjectConfig object with configuration options
     *
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'columns' => 'title',
        ));

        parent::_initialize($config);
    }

    /**
     * Insert the model states
     *
     * @param KObjectMixable $mixer
     */
    public function onMixin(KObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        $mixer->getState()
            ->insert('search', 'string')
            ->insert('search_by', 'string', 'exact');
    }

    /**
     * Add search query
     *
     * @param   KModelContextInterface $context A model context object
     *
     * @return    void
     */
    protected function _buildQuery(KModelContextInterface $context)
    {
        $state = $context->state;
        $search = $state->search;

        $combination = $context->_combination ?? 'AND';

        $model = $context->getSubject();

        if ($model instanceof KModelDatabase && !$context->state->isUnique())
        {
            list($conditions, $binds) = $this->_getConditions($search, $context);

            if ($conditions)
            {
                $context->query->where('(' . implode(' OR ', $conditions) . ')', $combination);

                foreach ($binds as $key => $value) {
                    $context->query->bind(array($key => $value));
                }
            }
        }
    }

    protected function _getConditions($search, KModelContextInterface $context)
    {
        $state = $context->state;

        $prefix = $context->_prefix ?? '';

        $conditions = [];
        $binds      = [];

        if ($search)
        {
            $search_column = null;
    
            // Parse $state->search for possible column prefix
            if (preg_match('#^([a-z0-9\-_]+)\s*:\s*(.+)\s*$#i', $search, $matches))
            {
                if (in_array($matches[1], $this->_columns) || $matches[1] === 'id') {
                    $search_column = $matches[1];
                    $search        = $matches[2];
                }
            }
    
            // Search in the form of id:NUM
            if ($search_column !== 'id')
            {
                $ignore = isset($context->_ignore_columns) ? (array) $context->_ignore_columns : []; 

                foreach ($this->_columns as $column)
                {
                    if ((!$search_column || $column === $search_column) && !in_array($column, $ignore))
                    {
                        $alias = $this->_alias_map[$column] ?? 'tbl';
                        
                        switch ($state->search_by)
                        {
                            case 'any':
        
                                $conditions[] = $alias . '.' . $column . ' RLIKE :search' . $prefix;

                                if (empty($binds)) {
                                    $binds['search' . $prefix] = implode('|', explode(' ', $search));
                                }
         
                                break;

                            case 'all':

                                $i = 0;

                                $subconditions = [];

                                foreach (explode(' ', $search) as $keyword)
                                {
                                    $subconditions[] = $alias . '.' . $column . " LIKE :search$prefix$i";
        
                                    $binds["search$prefix$i"] = '%'.$keyword.'%';
        
                                    $i++;
                                }

                                $conditions[] = '(' . implode(' AND ', $subconditions) . ')';
        
                                break;

                            case 'exact':      
                            default:
        
                                $conditions[] = $alias . '.'  . $column . " LIKE :search" . $prefix;

                                if (empty($binds)) {
                                    $binds['search' . $prefix] = '%' . $search . '%';
                                }
        
                                break;
                        }
                    }
                }
            }
            else
            {
                $conditions[] = '(tbl.' . $this->getTable()->getIdentityColumn() . ' = :search' . $prefix . ')';
                $binds['search' . $prefix] = $search;
            }
        }

        return [$conditions, $binds];
    }
}