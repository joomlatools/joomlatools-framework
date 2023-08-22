<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Json View
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\View
 */
class KViewJson extends KViewAbstract
{
    /**
     * JSON API version
     *
     * @var string
     */
    protected $_version;

    /**
     * A list of fields to use in the response. Blank for all.
     *
     * Comes from the comma separated "fields" value in the request
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * A list of text fields in the row
     *
     * URLs will be converted to fully qualified ones in these fields.
     *
     * @var string
     */
    protected $_text_fields;

    /**
     * Constructor
     *
     * @param   KObjectConfig $config Configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_version = $config->version;

        $this->_text_fields = KObjectConfig::unbox($config->text_fields);
        $this->_fields      = KObjectConfig::unbox($config->fields);

        $query = $this->getUrl()->getQuery(true);
        if (!empty($query['fields']))
        {
            $fields = explode(',', rawurldecode($query['fields']));
            $this->_fields = array_merge($this->_fields, $fields);
        }
    }

    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'fields'      => array(),
            'text_fields' => array('description'), // Links are converted to absolute ones in these fields
        ));

        parent::_initialize($config);
    }

    /**
     * Render and return the views output
     *
     * If the view 'content'  is empty the output will be generated based on the model data, if it set it will
     * be returned instead.
     *
     * @param KViewContext  $context A view context object
     * @return string A RFC4627-compliant JSON string, which may also be embedded into HTML.
     */
    protected function _actionRender(KViewContext $context)
    {
        $content = $this->getContent();

        if (empty($content))
        {
            $content = $this->_renderData();
            $this->_processLinks($content);
        }

        if (is_array($content) || $content instanceof \Traversable) {
            $content = new KObjectConfigJson($content);
        }

        $this->setContent($content);

        return parent::_actionRender($context);
    }

    /**
     * Force the route to fully qualified and not escaped by default
     *
     * @param   string|array    $route   The query string used to create the route
     * @param   boolean         $fqr     If TRUE create a fully qualified route. Default TRUE.
     * @param   boolean         $escape  If TRUE escapes the route for xml compliance. Default FALSE.
     * @return  KDispatcherRouterRoute The route
     */
    public function getRoute($route = '', $fqr = true, $escape = false)
    {
        return parent::getRoute($route, $fqr, $escape);
    }

    /**
     * Returns the JSON data
     *
     * It converts relative URLs in the content to relative before returning the result
     *
     * @return array
     */
    protected function _renderData()
    {
        $model  = $this->getModel();
        $data   = $this->_getCollection($model->fetch());
        $output = array(
            'version' => $this->_version,
            'links' => array(
                'self' => array(
                    'href' => (string) $this->_getPageUrl(),
                    'type' => 'application/json; version=1.0',
                )
            ),
            'meta'     => array(),
            'entities' => $data,
            'linked'   => array()
        );

        if ($this->isCollection())
        {
            $total  = $model->count();
            $limit  = (int) $model->getState()->limit;
            $offset = (int) $model->getState()->offset;

            $output['meta'] = array(
                'offset'   => $offset,
                'limit'    => $limit,
                'total'	   => $total
            );

            if ($limit && $total-($limit + $offset) > 0)
            {
                $output['links']['next'] = array(
                    'href' => $this->_getPageUrl(array('offset' => $limit+$offset)),
                    'type' => 'application/json; version=1.0',
                );
            }

            if ($limit && $offset && $offset >= $limit)
            {
                $output['links']['previous'] = array(
                    'href' => $this->_getPageUrl(array('offset' => max($offset-$limit, 0))),
                    'type' => 'application/json; version=1.0',
                );
            }
        }

        return $output;
    }

    /**
     * Returns the JSON representation of a collection
     *
     * @param  KModelEntityInterface $collection
     * @return array
     */
    protected function _getCollection(KModelEntityInterface $collection)
    {
        $result = array();

        foreach ($collection as $entity) {
            $result[] = $this->_getEntity($entity);
        }

        return $result;
    }

    /**
     * Get the item data
     *
     * @param KModelEntityInterface  $entity   Document row
     * @return array The array with data to be encoded to json
     */
    protected function _getEntity(KModelEntityInterface $entity)
    {
        $method = '_get'.ucfirst($entity->getIdentifier()->name);

        if ($method !== '_getEntity' && method_exists($this, $method)) {
            $data = $this->$method($entity);
        } else {
            $data = $entity->toArray();
        }

        if (!empty($this->_fields)) {
            $data = array_intersect_key($data, array_merge(array('links' => 'links'), array_flip($this->_fields)));
        }

        if (!isset($data['links'])) {
            $data['links'] = array();
        }

        if (!isset($data['links']['self']))
        {
            $data['links']['self'] = array(
                'href' => (string) $this->_getEntityRoute($entity),
                'type' => 'application/json; version=1.0',
            );
        }

        return $data;
    }

    /**
     * Get the item link
     *
     * @param KModelEntityInterface  $entity
     * @return string
     */
    protected function _getEntityRoute(KModelEntityInterface $entity)
    {
        $package = $this->getIdentifier()->package;
        $view    = $entity->getIdentifier()->name;

        return $this->getRoute(sprintf('component=%s&view=%s&slug=%s&format=json', $package, $view, $entity->slug));
    }

    /**
     * Get the page link
     *
     * @param  array  $query Additional query parameters to merge
     * @return string
     */
    protected function _getPageUrl(array $query = array())
    {
        $url = $this->getUrl();

        if ($query) {
            $url->setQuery(array_merge($url->getQuery(true), $query));
        }

        return (string) $url;
    }

    /**
     * Converts links in an array from relative to absolute
     *
     * @param array $array Source array
     */
    protected function _processLinks(array &$array)
    {
        $base = $this->getUrl()->toString(KHttpUrl::AUTHORITY);

        foreach ($array as $key => &$value)
        {
            if (is_array($value)) {
                $this->_processLinks($value);
            }
            elseif ($key === 'href')
            {
                $value = $value ?? ''; // Replace with empty string if null
                
                if (substr($value, 0, 4) !== 'http') {
                    $array[$key] = $base.$value;
                }
            }
            elseif (in_array($key, $this->_text_fields)) {
                $array[$key] = $this->_processText($value);
            }
        }
    }

    /**
     * Convert links in a text from relative to absolute and runs them through JRoute
     *
     * @param string $text The text processed
     * @return string Text with converted links
     */
    protected function _processText($text)
    {
        $matches = array();

        preg_match_all("/(href|src)=\"(?!http|ftp|https|mailto|data)([^\"]*)\"/", $text ?: '', $matches, PREG_SET_ORDER);

        foreach ($matches as $match)
        {
            $route = $this->getObject('lib:dispatcher.router.route', array(
                'url'    => $match[2],
                'escape' => false
            ));

            //Add the host and the schema
            $route->scheme = $this->getUrl()->scheme;
            $route->host   = $this->getUrl()->host;

            $text = str_replace($match[0], $match[1].'="'.$route.'"', $text);
        }

        return $text;
    }
}

