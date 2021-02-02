<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Controller Request
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Controller\Request
 */
class KControllerRequest extends KHttpRequest implements KControllerRequestInterface
{
    /**
     * The request query
     *
     * @var KHttpMessageParameters
     */
    protected $_query;

    /**
     * The request data
     *
     * @var KHttpMessageParameters
     */
    protected $_data;

    /**
     * The timestamp of the start of the request, with microsecond precision
     *
     * @var float
     */
    protected $_time;

    /**
     * Constructor
     *
     * @param KObjectConfig|null $config  An optional ObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Set query parameters
        $this->setQuery($config->query);

        //Set data parameters
        $this->setData($config->data);

        //Set the time
        $this->setTime($config->time);
    }

    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config  An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'query'  => array(),
            'data'   => array(),
            'format' => 'html',
            'time'   => microtime(true)
        ));

        parent::_initialize($config);
    }

    /**
     * The timestamp of the start of the request, with microsecond precision
     *
     * @return float
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     *T he timestamp of the start of the request, with microsecond precision
     *
     * @param $time
     * @return $thi
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /**
     * Return the request format or mediatype
     *
     * Find the format by using following sequence :
     *
     * 1. Use the the 'format' request parameter
     * 2. Use the URL path extension
     * 3. Use the accept header with the highest quality apply the reverse format map to find the format.
     *
     * @param   bool    $mediatype Get the media type
     * @return  string  The request format or NULL if no format could be found
     */
    public function getFormat($mediatype = false)
    {
        if (!isset($this->_format))
        {
            if(!$this->query->has('format')) {
                $format = parent::getFormat() ?: $this->getConfig()->format;
            } else {
                $format = $this->query->get('format', 'word');
            }

            $this->_format = $format;
        }

        return $mediatype ? static::$_formats[$this->_format][0] : $this->_format;
    }

    /**
     * Return the Url of the request regardless of the server
     *
     * @return  KHttpUrl A HttpUrl object
     */
    public function getUrl()
    {
        $url = parent::getUrl();

        //Add the query to the URL
        $url->setQuery($this->getQuery()->toArray());

        return $url;
    }

    /**
     * Set the request query
     *
     * @param  array $parameters
     * @return KControllerRequest
     */
    public function setQuery($parameters)
    {
        $this->_query = $this->getObject('lib:http.message.parameters', array('parameters' => $parameters));
        return $this;
    }

    /**
     * Get the request query
     *
     * @return KHttpMessageParameters
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Set the request data
     *
     * @param  array $parameters
     * @return KControllerRequest
     */
    public function setData($parameters)
    {
        $this->_data = $this->getObject('lib:http.message.parameters', array('parameters' => $parameters));
        return $this;
    }

    /**
     * Get the request query
     *
     * @return KHttpMessageParameters
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Implement a virtual 'headers', 'query' and 'data class property to return their respective objects.
     *
     * @param   string $name  The property name.
     * @return  mixed The property value.
     */
    public function __get($name)
    {
        $result = null;
        if($name == 'headers') {
            $result = $this->getHeaders();
        }

        if($name == 'query') {
            $result = $this->getQuery();
        }

        if($name == 'data') {
            $result =  $this->getData();
        }

        return $result;
    }

    /**
     * Deep clone of this instance
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();

        $this->_data  = clone $this->_data;
        $this->_query = clone $this->_query;
    }
}