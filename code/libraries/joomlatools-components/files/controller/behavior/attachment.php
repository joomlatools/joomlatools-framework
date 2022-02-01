<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachment Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesControllerBehaviorAttachment extends KControllerBehaviorAbstract
{
    /**
     * Attachments Controller.
     *
     * @var KControllerInterface|null
     */
    protected $_controller;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_controller = $config->controller;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array('controller' => 'attachment'));

        parent::_initialize($config);
    }

    /**
     * After Add command handler.
     *
     * Creates an attachment entity.
     *
     * @param KControllerContextInterface $context The context object.
     */
    protected function _afterAdd(KControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof ComFilesModelEntityNode && $entity->getStatus() !== KModelEntityInterface::STATUS_FAILED)
        {
            $controller = $this->_getController();
            $container  = $entity->getContainer();

            $attachment = $controller->getModel()->name($entity->name)->container($container->id)->fetch();

            if ($attachment->isNew())
            {
                $controller->getRequest()->getQuery()->set('container', $container->id);
                $controller->getModel()->container($container->id);

                $context->attachment = $controller->add(array('name' => $entity->name));
            }
            else $context->attachment = $attachment;
        }
    }

    /**
     * Attachment Controller getter.
     *
     * @return KControllerInterface
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof KControllerInterface)
        {
            $mixer = $this->getMixer();

            if (!$this->_controller instanceof KObjectIdentifierInterface)
            {
                if (strpos($this->_controller, '.') === false)
                {
                    $parts         = $mixer->getIdentifier()->toArray();
                    $parts['name'] = $this->_controller;

                    $identifier = $this->getIdentifier($parts);
                } else $identifier = $this->getIdentifier($this->_controller);
            } else $identifier = $this->_controller;

            $this->_controller = $this->getObject($identifier);
        }

        return $this->_controller;
    }
}