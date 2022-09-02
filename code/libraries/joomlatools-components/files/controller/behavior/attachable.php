<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesControllerBehaviorAttachable extends KControllerBehaviorAbstract
{
    /**
     * The attachment controller.
     *
     * @var KControllerInterface|null
     */
    protected $_controller;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_controller  = $config->controller;
    }

    /**
     * Before Attach command handler.
     *
     * Serves as a validation check.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _beforeAttach(KControllerContextInterface $context)
    {
        $entity = $context->getSubject()->getModel()->fetch();

        if ($entity->isNew()) {
            throw new RuntimeException('Entity does not exists');
        }

        $context->entity = $entity;
    }

    /**
     * Attach action.
     *
     * Forwards the action to the attachment controller with formatted data.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionAttach(KControllerContextInterface $context)
    {
        $this->_getController()->attach($this->_getData($context));
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
     * Forwards the action to the attachment controller with formatted data.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _actionDetach(KControllerContextInterface $context)
    {
        $this->_getController()->detach($this->_getData($context));
    }

    protected function _afterDetach(KControllerContextInterface $context)
    {
        $this->_afterAttach($context);
    }

    /**
     * Attachment controller getter.
     *
     * @return KControllerInterface
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof KControllerInterface)
        {
            $mixer = $this->getMixer();

            $parts         = $mixer->getIdentifier()->toArray();
            $parts['name'] = 'attachment';

            $identifier = $this->getIdentifier($parts);

            $query = $mixer->getRequest()->getQuery();
            $data  = $mixer->getRequest()->getData();

            $request = $this->getObject('lib:controller.request', array(
                'query' => array(
                    'name'      => $data->attachment,
                    'container' => $query->container
                )
            ));

            $this->_controller = $this->getObject($identifier, array('request'  => $request));
        }

        return $this->_controller;
    }

    /**
     * POST data getter.
     *
     * @param KControllerContextInterface $context The context object.
     * @return array The data
     */
    protected function _getData(KControllerContextInterface $context)
    {
        $entity = $context->entity;

        return array('table' => $entity->getTable()->getBase(), 'row' => $entity->id);
    }
}