<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Thumbnailable Model behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelBehaviorThumbnailable extends KModelBehaviorAbstract
{
    protected $_container;

    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()->insert('thumbnails', 'raw');
    }

    public function isSupported()
    {
        $result = false;

        if ($this->getMixer() instanceof ComFilesModelNodes) { // To protect against ::getContainer calls
            $result = true;
        }

        return $result;
    }

    /*
     * Cast thumbnails state values representing booleans to booleans values
     */
    protected function _beforeReset(KModelContextInterface $context)
    {
        if (in_array('thumbnails', $context->modified->toArray()))
        {
            $state = $this->getState();

            $value = $state->thumbnails;

            if (in_array($value, array('false', 'true', '0', '1')) || is_numeric($value)) {
                $state->offsetSet('thumbnails', filter_var($value, FILTER_VALIDATE_BOOLEAN));
            }
        }
    }

    public function getThumbnailsContainer()
    {
        if (!$this->_container  instanceof ComFilesModelEntityContainer && ($container = $this->getContainer()))
        {
            if ($slug = $container->getParameters()->thumbnails_container)
            {
                $container = $this->getObject('com:files.model.containers')
                                  ->slug($slug)
                                  ->fetch();

                if ($container->isNew()) {
                    throw new RuntimeException('Could not fetch thumbnails container');
                }

                $this->_container = $container->top();
            }
        }

        return $this->_container;
    }

    protected function _afterCreate(KModelContextInterface $context)
    {
        if ($container = $this->getThumbnailsContainer()) {
            $context->entity->thumbnails_container_slug = $container->slug;
        }
    }

    protected function _afterFetch(KModelContextInterface $context)
    {
        $state = $this->getState();

        $container = $this->getThumbnailsContainer();

        if ($container && $this->getContainer()->getParameters()->thumbnails)
        {
            $model = $this->getObject('com:files.model.thumbnails')->container($container->slug);

            if ($state->thumbnails !== true) {
                $model->version($state->thumbnails);
            }

            foreach ($context->entity as $entity)
            {
                $entity->thumbnails_container_slug = $container->slug;

                if ($state->thumbnails)
                {
                    $model->source($entity->uri);

                    $thumbnails = $model->fetch();

                    if ($thumbnails->isNew()) {
                        $thumbnails = false;
                    }

                    $entity->thumbnail = $thumbnails;
                }
            }
        }
    }
}