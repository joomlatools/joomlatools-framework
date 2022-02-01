<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachable Dispatcher Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesDispatcherBehaviorAttachable extends KBehaviorAbstract
{
    /**
     * The attachments container slug.
     *
     * @var string
     */
    protected $_container;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_container = $config->container;
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'container' => sprintf('%s-attachments',
                $config->mixer->getIdentifier()->getPackage())
        ));

        parent::_initialize($config);
    }

    /**
     * Before Dispatch command handler.
     *
     * Makes sure to forward requests to com_files or set container data to the request depending on the view.
     *
     * @param KDispatcherContextInterface $context The context object.
     *
     * @return bool True if the request should be dispatched, false otherwise.
     */
    protected function _beforeDispatch(KDispatcherContextInterface $context)
    {
        $result = true;

        $this->_setAliases();

        $query = $context->getRequest()->getQuery();

        if ($query->routed && in_array($query->view, array('file', 'files', 'node', 'nodes')))
        {
            $this->_forward($context);
            $this->send($context);
            $result = false;
        }
        elseif (in_array($query->view, array('attachment', 'attachments')))
        {
            $container = $this->getObject('com:files.model.containers')->slug($this->_container)->fetch();

            if (!$container->isNew()) {
                $context->getRequest()->getQuery()->container = $container->id;
            }
        }

        return $result;
    }

    /**
     * Alias setter.
     */
    protected function _setAliases()
    {
        $mixer = $this->getMixer();

        $aliases = array(
            'com:files.controller.permission.attachment' => array(
                'path' => array('controller', 'permission'),
                'name' => 'attachment'
            ),
            'com:files.controller.behavior.attachment'   => array(
                'path' => array('controller', 'behavior'),
                'name' => 'attachment'
            ),
            'com:files.controller.attachment'            => array(
                'path' => array('controller'),
                'name' => 'attachment'
            )
        );

        $manager = $this->getObject('manager');

        foreach ($aliases as $identifier => $alias)
        {
            $alias = array_merge($mixer->getIdentifier()->toArray(), $alias);

            if (!$manager->getClass($alias, false)) {
                $manager->registerAlias($identifier, $alias);
            }
        }
    }

    /**
     * Forwards the request to com_files.
     *
     * @param KDispatcherContextInterface $context The context object.
     */
    protected function _forward(KDispatcherContextInterface $context)
    {
        $mixer = $this->getMixer();

        $parts = $mixer->getIdentifier()->toArray();
        $parts['path'] = array('controller', 'permission');
        $parts['name'] = 'attachment';

        $permission = $this->getIdentifier($parts)->toString();

        $parts['path'] = array('controller', 'behavior');

        $behavior = $this->getIdentifier($parts)->toString();

        $parts['path'] = array('controller');

        $controller = $this->getIdentifier($parts)->toString();

        // Set controller on attachment behavior and push attachment permission to file controller.
        $this->getIdentifier('com:files.controller.file')
             ->getConfig()
             ->append(array('behaviors' => array($behavior => array('controller' => $controller), 'permissible' => array('permission' => $permission))));

        $context->getRequest()->getQuery()->container = $this->_container;
        $context->param = 'com:files.dispatcher.http';

        $this->forward($context);
    }
}