<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Resourceable Controller Behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Joomlatools\Component\LOGman
 */
class ComActivitiesControllerBehaviorResourceable extends KControllerBehaviorAbstract
{
    /**
     * A list of actions for cleaning up resources
     *
     * @var array
     */
    protected $_actions;

    /**
     * Resource controller
     *
     * @var KControllerInterface
     */
    protected $_controller = 'com:activities.controller.resource';

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_actions    = KObjectConfig::unbox($config->actions);
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'actions'    => array('delete')
        ));

        parent::_initialize($config);
    }

    protected function _afterAdd(KControllerContextInterface $context)
    {
        $entity = $context->result;

        if ($entity instanceof ComActivitiesActivityInterface && $entity->getActivityObject())
        {
            $resource = $this->_getResource($entity);

            $controller = $this->_getController();

            if (!in_array($entity->action, $this->_actions))
            {
                $data = $this->_getData($entity);

                if (!$resource->isNew())
                {
                    // Update resource if title changed.
                    if ($resource->title != $entity->title) {
                        $controller->id($resource->id)->edit($data);
                    }
                }
                else $controller->add($data);
            }
            else if (!$resource->isNew()) $controller->id($resource->id)->delete();
        }
    }

    /**
     * Resource getter.
     *
     * @param KModelEntityInterface $entity The entity to get the resource from
     *
     * @return KModelEntityInterface|null The resource
     */
    protected function _getResource($entity)
    {
        $model = $this->_getController()->getModel();

        $model->reset()->getState()->setValues(array(
            'package'     => $entity->package,
            'name'        => $entity->name,
            'resource_id' => $entity->row
        ));

        return $model->fetch();
    }

    /**
     * Entity data getter
     *
     * @param KModelEntityInterface $entity The entity to get data from
     *
     * @return array The entity data
     */
    protected function _getData(KModelEntityInterface $entity)
    {
        $data = array(
            'package'     => $entity->package,
            'name'        => $entity->name,
            'resource_id' => $entity->row,
            'title'       => $entity->title
        );

        if ($uuid = $entity->getActivityObject()->getUuid()) {
            $data['uuid'] = $uuid;
        }

        return $data;
    }

    /**
     * Resource controller getter.
     *
     * @return KControllerInterface The controller
     */
    protected function _getController()
    {
        if (!$this->_controller instanceof KControllerInterface) {
            $this->_controller = $this->getObject($this->_controller);
        }

        return $this->_controller;
    }
}