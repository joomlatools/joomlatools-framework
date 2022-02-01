<?php
/**
 * Tagging Component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */


/**
 * Taggable Controller Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsModelBehaviorTaggable extends KModelBehaviorAbstract
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('before.create' , '_makeTaggable');
        $this->addCommandCallback('before.fetch'  , '_makeTaggable');
        $this->addCommandCallback('before.count'  , '_makeTaggable');
    }

    /**
     * High priority is used so that the table object is made taggable before paginatable kicks in
     *
     * @param KObjectConfig $config
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => KBehaviorAbstract::PRIORITY_HIGH,
            'strict'   => false
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

        //Insert the tag model state
        $mixer->getState()->insert('tag', 'slug');
    }

    /**
     * Make the model entity taggable
     *
     * @param KModelContextInterface $context
     */
    protected function _makeTaggable(KModelContextInterface $context)
    {
        $model = $context->getSubject();

        $model->getTable()->addBehavior('com:tags.database.behavior.taggable', array(
            'tags'   => $model->getState()->tag,
            'strict' => $this->getConfig()->strict
        ));
    }

    /**
     * Bind the tag to query
     *
     * @param   KModelContextInterface $context A model context object
     * @return  void
     */
    protected function _beforeFetch(KModelContextInterface $context)
    {
        $model = $context->getSubject();

        if ($model instanceof KModelDatabase)
        {
            $state = $context->state;

            if ($state->tag) {
                $context->query->bind(array('tag' => $state->tag));
            }
        }
    }

    /**
     * Bind the tag to query
     * @param KModelContextInterface $context
     */
    protected function _beforeCount(KModelContextInterface $context)
    {
        $this->_beforeFetch($context);
    }
}
