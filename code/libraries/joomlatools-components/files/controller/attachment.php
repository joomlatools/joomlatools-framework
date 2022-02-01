<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachment Controller
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesControllerAttachment extends ComKoowaControllerModel
{
    protected function _initialize(KObjectConfig $config)
    {
        if ($this->getIdentifier()->package != 'files')
        {
            $aliases = array(
                'com:files.model.attachments'                => array(
                    'path' => array('model'),
                    'name' => 'attachments'
                ),
                'com:files.model.behavior.attachable'        => array(
                    'path' => array('model', 'behavior'),
                    'name' => 'attachable'
                ),
                'com:files.controller.permission.attachment' => array(
                    'path' => array('controller', 'permission'),
                    'name' => 'attachment'
                )
            );

            $manager = $this->getObject('manager');

            foreach ($aliases as $identifier => $alias)
            {
                $alias = array_merge($this->getIdentifier()->toArray(), $alias);

                if (!$manager->getClass($alias, false)) {
                    $manager->registerAlias($identifier, $alias);
                }
            }
        }

        parent::_initialize($config);
    }

    /**
     * Before Render command handler.
     *
     * Pushes permissions to the view.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _beforeRender(KControllerContextInterface $context)
    {
        $view = $this->getView();

        $view->getConfig()->append(array(
            'config' => array(
                'can_attach' => $this->canAttach(),
                'can_detach' => $this->canDetach()
            )
        ));
    }

    /**
     * Before Attach command handler.
     *
     * Makes sure that there's an attachment and that this attachment exists.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _beforeAttach(KControllerContextInterface $context)
    {
        $model = $this->getModel();

        $column = $model->getTable()->getIdentityColumn();

        $context->identity_column = $column;

        if (!$context->attachment) {
            $context->attachment = $this->getModel()->fetch();
        }

        if ($context->attachment->isNew())
        {
            $state = $model->getState();

            $container = $this->getObject('com:files.model.containers')->id($state->container)->fetch();

            $file = $this->getObject('com:files.model.files')->container($container->slug)->name($state->name)->fetch();

            // Check if a file in the given container exists.
            if (!$file->isNew())
            {
                // Create the attachment entry.
                $controller = $this->getObject($this->getIdentifier());
                $controller->getRequest()->getQuery()->container = $this->getRequest()->getQuery()->container;
                $context->attachment = $controller->add(array('name' => $model->getState()->name));
            }
            else throw new RuntimeException('Attachment does not exists');
        }
    }

    /**
     * Attach action.
     *
     * Creates a relationship between a resource and an existing attachment.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionAttach(KControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();
        $data  = $context->getRequest()->getData();

        $data[$context->identity_column] = $context->attachment->id;

        $relation = $model->create($data->toArray());

        if (!$relation->save()) {
            throw new RuntimeException('Could not attach');
        }

        $context->relation = $relation;
    }

    protected function _afterAttach(KControllerContextInterface $context)
    {
        $context->getResponse()->setStatus(KHttpResponse::NO_CONTENT);
    }

    protected function _beforeDetach(KControllerContextInterface $context)
    {
        $this->_beforeAttach($context);
    }

    /**
     * Detach action.
     *
     * Removes a relationship between a resource and an existing attachment.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionDetach(KControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();

        $relation = $model->{$context->identity_column}($context->attachment->id)
                          ->setState($this->getRequest()->getData()->toArray())->fetch();

        if (!$relation->isNew())
        {
            if (!$relation->delete()) {
                throw new RuntimeException('Could not detach');
            }
        }
    }

    protected function _afterDetach(KControllerContextInterface $context)
    {
        $model = $this->getModel()->getRelationsModel();

        $model->getState()->reset();

        $attachment = $context->attachment;

        if (!$model->{$context->identity_column}($attachment->id)->count())
        {
            if (!$attachment->delete()) {
                throw new RuntimeException(('Attachment could not be deleted'));
            }
        }

        $this->_afterAttach($context);
    }

    /**
     * Overriden for auto-aliasing views when the controller is extended.
     */
    public function setView($view)
    {
        $view = parent::setView($view);

        if ($view instanceof KObjectIdentifierInterface && $view->getPackage() !== 'files')
        {
            $manager = $this->getObject('manager');

            if (!$manager->getClass($view, false))
            {
                $identifier = $view->toArray();
                $identifier['package'] = 'files';
                unset($identifier['domain']);

                $manager->registerAlias($identifier, $view);
            }
        }

        return $view;
    }
}