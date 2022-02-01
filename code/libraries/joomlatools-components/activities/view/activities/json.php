<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activities JSON View.
 *
 * JSON view has support for the 'stream' layout. If layout is stream (...&layout=stream) the output will be rendered
 * according to the Activity Streams Specification.
 *
 * @link     http://activitystrea.ms/specs/json/1.0/#json
 *
 * @author   Arunas Mazeika <https://github.com/amazeika>
 * @package  Koowa\Component\Activities
 */
class ComActivitiesViewActivitiesJson extends KViewJson
{
    /**
     * JSON layout [stream].
     *
     * @var mixed
     */
    protected $_layout;

    /**
     * Activities renderer.
     *
     * @var mixed
     */
    protected $_renderer;

    /**
     * Constructor.
     *
     * @param KObjectConfig $config Configuration options.
     */
    public function __construct(KObjectConfig $config)
    {
        $this->_layout = $config->layout;

        parent::__construct($config);

        $this->_renderer = $config->renderer;
    }

    /**
     * Initializes the options for the object.
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param KObjectConfig $config Configuration options.
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'renderer'  => 'activity'
        ));

        parent::_initialize($config);
    }

    /**
     * Get the entity data
     *
     * @link http://activitystrea.ms/specs/json/1.0/#json See JSON serialization.
     *
     * @param KModelEntityInterface $entity The model entity.
     * @return array The array with data to be encoded to JSON.
     */
    protected function _getEntity(KModelEntityInterface $entity)
    {
        if ($this->_layout == 'stream')
        {
            $activity = $entity;
            $renderer = $this->getRenderer();

            $item = array(
                'id'        => $activity->getActivityId(),
                'title'     => $renderer->render($activity, array('escaped_urls' => false, 'fqr' => true)),
                'story'     => $renderer->render($activity, array('html' => false)),
                'published' => $activity->getActivityPublished()->format('c'),
                'verb'      => $activity->getActivityVerb(),
                'format'    => $activity->getActivityFormat(),
                'locale'  => $activity->getLocale()
            );

            if ($icon = $activity->getActivityIcon()) {
                $item['icon'] = $this->_getMediaLinkData($icon);
            }

            foreach ($activity->objects as $name => $object)
            {
                $clone = clone $object;

                if ($object->isTranslatable())
                {
                    $translator = $activity->getTranslator();
                    $clone->setDisplayName($translator->translateActivityToken($clone, $activity));
                }

                $item[$name] = $this->_getObjectData($clone);
            }
        }
        else
        {
            $item = $entity->toArray();
            if (!empty($this->_fields)) {
                $item = array_intersect_key($item, array_flip($this->_fields));
            }
        }

        return $item;
    }

    /**
     * Get the activity renderer.
     *
     * @throws UnexpectedValueException if renderer has the wrong type.
     * @return ComActivitiesActivityRendererInterface The activity renderer.
     */
    public function getRenderer()
    {
        if (!$this->_renderer instanceof KTemplateHelperInterface)
        {
            // Make sure we have an identifier
            if(!($this->_renderer instanceof KObjectIdentifier)) {
                $this->setRenderer($this->_renderer);
            }

            $this->_renderer = $this->getObject($this->_renderer);

            if(!$this->_renderer instanceof ComActivitiesActivityRendererInterface)
            {
                throw new UnexpectedValueException(
                    'Renderer: '.get_class($this->_renderer).' does not implement ComActivitiesActivityRendererInterface'
                );
            }

            $this->_renderer->getTemplate()->registerFunction('url', array($this, 'getUrl'));
        }

        return $this->_renderer;
    }

    /**
     * Set the activity renderer.
     *
     * @param mixed $renderer An activity renderer instance, identifier object or string.
     * @return ComActivitiesViewActivitiesJson
     */
    public function setRenderer($renderer)
    {
        if(!$renderer instanceof ComActivitiesActivityRendererInterface)
        {
            if(is_string($renderer) && strpos($renderer, '.') === false )
            {
                $identifier         = $this->getIdentifier()->toArray();
                $identifier['path'] = array('template', 'helper');
                $identifier['name'] = $renderer;

                $identifier = $this->getIdentifier($identifier);
            }
            else $identifier = $this->getIdentifier($renderer);

            $renderer = $identifier;
        }

        $this->_renderer = $renderer;

        return $this;
    }

    /**
     * Activity object data getter.
     *
     * @param ComActivitiesActivityObjectInterface $object The activity object.
     * @return array The object data.
     */
    protected function _getObjectData(ComActivitiesActivityObjectInterface $object)
    {
        $data = $object->toArray();

        // Make sure we get fully qualified URLs.
        if ($url = $object->getUrl()) {
            $data['url'] = $this->_getUrl($url);
        }

        $attachments = array();

        // Handle attachments recursively.
        foreach ($object->getAttachments() as $attachment) {
            $attachments[] = $this->_getObjectData($attachment);
        }

        $data['attachments'] = $attachments;

        // Convert date objects to date time strings.
        foreach (array('published', 'updated') as $property)
        {
            $method = 'get' . ucfirst($property);

            if ($date = $object->$method()) {
                $data[$property] = $date->format('M d Y H:i:s');
            }
        }

        foreach ($object as $key => $value)
        {
            if ($value instanceof ComActivitiesActivityObjectInterface) {
                $data[$key] = $this->_getObjectData($value);
            }

            if ($value instanceof ComActivitiesActivityMedialinkInterface) {
                $data[$key] = $this->_getMedialinkData($value);
            }
        }

        return $this->_cleanupData($data);
    }

    /**
     * Activity medialink data getter.
     *
     * @param ComActivitiesActivityMedialinkInterface $medialink The medialink object.
     * @return array The object data.
     */
    protected function _getMedialinkData(ComActivitiesActivityMedialinkInterface $medialink)
    {
        $data = $medialink->toArray();

        $data['url'] = $this->_getUrl($medialink->getUrl());

        return $this->_cleanupData($data);
    }

    /**
     * Removes entries with empty values.
     *
     * @param array $data The data to cleanup.
     * @return array The cleaned up data.
     */
    protected function _cleanupData(array $data = array())
    {
        $clean = array();

        foreach ($data as $key => $value)
        {
            if (!empty($value)) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /**
     * URL getter.
     *
     * Provides a fully qualified and un-escaped URL provided a URL object.
     *
     * @param KHttpUrlInterface $url The URL.
     * @return string The fully qualified un-escaped URL.
     */
    protected function _getUrl(KHttpUrlInterface $url)
    {
        if (!$url->getHost() && !$url->getScheme()) {
            $url->setUrl($this->getUrl()->toString(KHttpUrl::AUTHORITY));
        }

        return $url->toString(KHttpUrl::FULL, false);
    }
}
