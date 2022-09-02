<?php
/**
 * Tagging component for Joomlatools Framework - http://developer.joomlatools.com/framework
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
class ComTagsControllerBehaviorTaggable extends KBehaviorAbstract
{
    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.add'    , '_setTags');
        $this->addCommandCallback('after.edit'   , '_setTags');
        $this->addCommandCallback('before.delete' , '_deleteTags');
    }

    /**
     * Set the tags for an entity
     *
     * If the request data contains a tags array, it will be used as the new tag list.
     * If the tags field is an empty string, all entity tags are deleted and no new ones are added.
     *
     * Operation mode can be controlled by the tags_operation in the request data.
     * Possible values are append|remove|replace, replace being the default.
     *
     * @param KControllerContextInterface $context
     * @return bool
     */
    protected function _setTags(KControllerContextInterface $context)
    {
        $entities = $context->result;
        $data     = $context->getRequest()->getData();

        if ($data->has('tags') && !$context->response->isError())
        {
            foreach($entities as $entity)
            {
                if ($entity->isIdentifiable())
                {
                    $operation = $entity->tags_operation;

                    if ($operation === 'remove') {
                        $this->_removeTags($entity);
                    } else if ($operation === 'append') {
                        $this->_appendTags($entity);
                    } else {
                        $this->_replaceTags($entity);
                    }
                }
            }
        }
    }

    /**
     * Replaces the entity tags with the ones sent in the request
     *
     * @param KModelEntityInterface $entity
     */
    protected function _replaceTags(KModelEntityInterface $entity)
    {
        $tags = $entity->getTags();

        //Delete tags
        if(count($tags))
        {
            $tags->delete();
            $tags->reset();
        }

        $this->_appendTags($entity);
    }

    /**
     * Appends the tags sent in the request to the entity
     *
     * @param KModelEntityInterface $entity
     */
    protected function _appendTags(KModelEntityInterface $entity)
    {
        $identifier = [
            'type' => 'com',
            'package' => $this->getMixer()->getIdentifier()->package,
            'path' => ['controller', 'tag'],
        ];

        if ($domain = $this->getMixer()->getIdentifier()->domain) {
            $identifier['domain'] = $domain;
        }

        if(!$this->getObject($identifier)->canAdd()) {
            $status  = KDatabase::STATUS_FETCHED;
        } else {
            $status = null;
        }

        $tags     = $entity->getTags();
        $existing = array();

        foreach ($tags as $tag) {
            $existing[] = $tag->title;
        }

        //Create tags
        if($entity->tags)
        {
            foreach ($entity->tags as $tag)
            {
                if (in_array($tag, $existing)) {
                    continue;
                }

                $config = array(
                    'data' => array(
                        'title' => $tag,
                        'row'   => $entity->uuid,
                    ),
                    'status' => $status,
                );

                $row = $tags->getTable()->createRow($config);

                $tags->insert($row);
                $tags->save();
            }
        }
    }

    /**
     * Removes the tags sent in the request from the entity
     * @param KModelEntityInterface $entity
     */
    protected function _removeTags(KModelEntityInterface $entity)
    {
        $tags = $entity->getTags();

        foreach ($tags as $tag)
        {
            if (in_array($tag->title, $entity->tags)) {
                $tag->delete();
            }
        }
    }

    /**
     * Remove tags from an entity after it is deleted
     *
     * @param KControllerContextInterface $context
     * @return void
     */
    protected function _deleteTags(KControllerContextInterface $context)
    {
        $entities = $this->getModel()->fetch();

        foreach ($entities as $entity)
        {
            $status = $entity->getStatus();

            if ($entity->isIdentifiable() && $status != $entity::STATUS_DELETED) {
                $entity->getTags()->delete();
            }
        }
    }
}
