<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Relatable Model behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelBehaviorRelatable extends KModelBehaviorAbstract
{
    /**
     * Relations model.
     *
     * @var KModelInterface|string|KObjectIdentifierInterface
     */
    protected $_model;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_model = $config->model;
        $this->_columns = KObjectConfig::unbox($config->columns);
    }

    protected function _initialize(KObjectConfig $config)
    {
        if ($mixer = $config->mixer) {
            $model = sprintf('%s_%s', $mixer->getIdentifier()->getName(), 'relations');
        } else {
            $model = 'relations';
        }

        $config->append(array('model' => $model, 'columns' => array('table' => 'cmd', 'row' => 'cmd')));
        parent::_initialize($config);
    }

    /**
     * Before Fetch command handler.
     *
     * Adds joins and where statements.
     *
     * @param KModelContextInterface $context The context object.
     */
    protected function _beforeFetch(KModelContextInterface $context)
    {
        $query = $context->query;

        $state = $context->getState();

        if (array_intersect(array_keys($state->getValues()), array_keys($this->_columns)))
        {
            $table  = $this->getRelationsModel()->getTable()->getBase();
            $column = $this->getTable()->getIdentityColumn();

            $query->join($table . ' AS relations', 'relations.' . $column . ' = tbl.' . $column, 'INNER');


            foreach (array_keys($this->_columns) as $column)
            {
                if ($state->{$column}) {
                    $query->where(sprintf('relations.%1$s = :%1$s', $column))->bind(array($column => $state->{$column}));
                }
            }
        }
    }

    /**
     * Before Count command handler.
     *
     * Adds joins and where statements.
     *
     * @param KModelContextInterface $context The context object.
     */
    protected function _beforeCount(KModelContextInterface $context)
    {
        $this->_beforeFetch($context); // Same as fetch.
    }

    /**
     * Insert the model states
     *
     * @param KObjectMixable $mixer
     */
    public function onMixin(KObjectMixable $mixer)
    {
        parent::onMixin($mixer);

        if ($mixer instanceof KModelDatabase)
        {
            foreach ($this->_columns as $name => $filter) {
                $mixer->getState()->insert($name, $filter);
            }
        }
    }

    /**
     * Relations Model getter.
     *
     * @return KModelInterface
     */
    public function getRelationsModel()
    {
        if (!$this->_model instanceof KModelInterface)
        {
            $identifier = $this->_model;

            if (is_string($identifier))
            {
                if (strpos($identifier, '.') === false)
                {
                    $identifier = $this->getMixer()->getIdentifier()->toArray();
                    $identifier['name'] = $this->_model;
                }

                $identifier = $this->getIdentifier($identifier);
            }

            $this->_model = $this->getObject($identifier, array(
                'relation_column' => $this->getTable()
                                          ->getIdentityColumn()
            ));
        }

        return $this->_model;
    }
}