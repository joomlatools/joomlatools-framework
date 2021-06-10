<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Http Client
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Http\Client
 */
class KHttpClient extends KObject implements KHttpClientInterface
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation
     *
     * @param   KObjectConfig $config  An optional KObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'user_agent'      => 'Joomlatools/Framework/'. Koowa::getInstance()->getVersion(),
            'follow_location' => 0,
        ));

        parent::_initialize($config);
    }

    /**
     * Send a http request
     *
     * @param  KHttpRequestInterface $request   The http request object
     * @throws KHttpException[Status] If the request failed
     * @return  KHttpResponseInterface
     */
    public function send(KHttpRequestInterface $request)
    {
        if(!ini_get('allow_url_fopen')) {
            throw new RuntimeException('Cannot use a stream transport when "allow_url_fopen" is disabled.');
        }

        $headers = $request->getHeaders()
            ->set('Connection', 'close');

        $context = stream_context_create(array('http' => array(
            'user_agent'       => $this->getConfig()->user_agent,
            //'protocol_version' => $request->getVersion(),
            'header'           => (string) $headers,
            'follow_location ' => $this->getConfig()->follow_location,
            'method'           => $request->getMethod(),
            'content'          => (string) $request->getContent(),
        )));

        $url     = $request->getUrl();
        $content = @file_get_contents($url, false, $context);

        if($content === false) {
            throw new KHttpExceptionError(sprintf('Failed to establish connection to: "%s"', $url));
        }

        $response = $this->_createResponse($http_response_header);

        if($content) {
            $response->setContent($content);
        }

        //Request failed
        if($response->isError())
        {
            $code    = $response->getStatusCode();
            $message = $response->getStatusMessage();

            switch($code)
            {
                case 400: $exception = new KHttpExceptionBadRequest($message); break;
                case 401: $exception = new KHttpExceptionUnauthorized($message); break;
                case 403: $exception = new KHttpExceptionForbidden($message); break;
                case 404: $exception = new KHttpExceptionNotFound($message); break;
                case 405: $exception = new KHttpExceptionMethodNotAllowed($message); break;
                case 406: $exception = new KHttpExceptionNotAcceptable($message); break;
                case 409: $exception = new KHttpExceptionConflict($message); break;
                case 415: $exception = new KHttpExceptionUnsupportedMediaType($message); break;
                default: $exception = new  KHttpExceptionError($message, $code);
            }

            //Throw the exception
            throw $exception;
        }

        return $response;
    }

    /**
     * Send a GET request
     *
     * If successfull and the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-24
     *
     * @param string $url  The endpoint url
     * @param array $headers Optional request headers
     * @return array|string
     */
    public function get($url, $headers = array())
    {
        $request = $this->_createRequest($url, array(), $headers)
            ->setMethod(KHttpRequest::GET);

        $response = $this->send($request);

        return $this->parseResponseContent($response);
    }

    /**
     * Send a POST request
     *
     * If successfull and the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-25
     *
     * @param string $url  The endpoint url
     * @param array|KObjectConfigFormat $data The data to send. If the data is an array it will be urlencoded.
     * @param array $headers Optional request headers
     * @return array|string
     */
    public function post($url, $data, $headers = array())
    {
        $request = $this->_createRequest($url, $data, $headers)
            ->setMethod(KHttpRequest::POST);

        $response = $this->send($request);

        return $this->parseResponseContent($response);
    }

    /**
     * Send a PUT request
     *
     * If successfull and the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-26
     *
     * @param string $url  The endpoint url
     * @param array|KObjectConfigFormat $data The data to send. If the data is an array it will be urlencoded.
     * @param array $headers Optional request headers
     * @return array|string
     */
    public function put($url, $data, $headers = array())
    {
        $request = $this->_createRequest($url, $data, $headers)
            ->setMethod(KHttpRequest::PUT);

        $response = $this->send($request);

        return $this->parseResponseContent($response);
    }

    /**
     * Send a PATCH request
     *
     * If successfull and the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc5789
     *
     * @param string $url  The endpoint url
     * @param array|KObjectConfigFormat $data The data to send. If the data is an array it will be urlencoded.
     * @param array $headers Optional request headers
     * @return array|string
     */
    public function patch($url, $data, $headers = array())
    {
        $request = $this->_createRequest($url, $data, $headers)
            ->setMethod(KHttpRequest::PATCH);

        $response = $this->send($request);

        return $this->parseResponseContent($response);
    }

    /**
     * Send a DELETE request
     *
     * If successfull and the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-29
     *
     * @param string $url  The endpoint url
     * @param array|KObjectConfigFormat $data The data to send. If the data is an array it will be urlencoded.
     * @param array $headers Optional request headers
     * @return array|string
     */
    public function delete($url, $data = array(), $headers = array())
    {
        $request = $this->_createRequest($url, $data, $headers)
            ->setMethod(KHttpRequest::DELETE);

        $response = $this->send($request);

        return $this->parseResponseContent($response);
    }

    /**
     * Send a OPTIONS request
     *
     * If successfull the response headers will returned as an array. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-31
     *
     * @param string $url  The endpoint url
     * @param array $headers Optional request headers
     * @return array|false
     */
    public function options($url, $headers = array())
    {
        $request = $this->_createRequest($url, [], $headers)
            ->setMethod(KHttpRequest::OPTIONS);

        $response = $this->send($request);

        return $response->isSuccess() ? $response->getHeaders()->toArray(): false;
    }

    /**
     * Send a HEAD request
     *
     * If successfull the response headers will returned as an array. If the request fails FALSE will be returned.
     *
     * @link https://tools.ietf.org/html/rfc7231#page-25
     *
     * @param string $url  The endpoint url
     * @param array $headers Optional request headers
     * @return array|false
     */
    public function head($url, $headers = array())
    {
        $request = $this->_createRequest($url, [], $headers)
            ->setMethod(KHttpRequest::HEAD);

        $response = $this->send($request);

        return $response->isSuccess() ? $response->getHeaders()->toArray(): false;
    }

    /**
     * Parse the response content
     *
     * If the response content format is known, the content will returned as an array, if the content
     * cannot be unserialised it will be returned directly. If the response is not successfull FALSE will be returned.
     *
     * @param KHttpResponseInterface $response
     * @return array
     */
    public function parseResponseContent(KHttpResponseInterface $response)
    {
        $result = false;

        $format = $response->getFormat();
        $result = $response->getContent();

        if($this->getObject('object.config.factory')->isRegistered($format)) {
            $result = $this->getObject('object.config.factory')->createFormat($format)->fromString($result, false);
        }

        return $result;
    }

    /**
     * Create a request object
     *
     * @param string $url  The endpoint url
     * @param array|KObjectConfigFormat $data The data to send. If the data is an array it will be urlencoded.
     * @param array $headers Optional request headers
     * @return KHttpRequestInterface
     */
    protected function _createRequest($url, $data = array(), $headers = array())
    {
        if (!is_array($data) && !($data instanceof KObjectConfigFormat))
        {
            throw new UnexpectedValueException(
                'The request data must be an array or an object implementing KObjectConfigFormat, "'.gettype($data).'" given.'
            );
        }

        $request = $this->getObject('http.request')->setUrl($url);

        //Add additional headers
        $request->getHeaders()->add($headers);

        //Send content
        if(is_array($data))
        {
            if(!empty($data))
            {
                $content = http_build_query($data, '', '&');
                $request->setContent($content, 'application/x-www-form-urlencoded');
            }
        }
        else $request->setContent((string) $data, $data->getMediaType());

        return $request;
    }

    /**
     * Create a response object
     *
     * @param array $headers
     * @return KHttpResponseInterface
     */
    protected function _createResponse(array $headers)
    {
        if(empty($headers)) {
            $headers = array('HTTP/1.1 400 Bad request');
        }

        $status = explode(' ', array_shift($headers), 3);

        $response = $this->getObject('http.response', [
            'status_code'    => $status[1],
            'status_message' => isset($status[2]) ? $status[2] : '',
        ]);

        foreach($headers as $value )
        {
            $parts = explode( ':', $value, 2 );
            if(isset($parts[1])) {
                $response->getHeaders()->set(trim($parts[0]), trim($parts[1]));
            }
        }

        return $response;
    }
}
