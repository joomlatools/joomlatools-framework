<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Abstract Dispatcher Request
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Request
 */
abstract class KDispatcherRequestAbstract extends KControllerRequest implements KDispatcherRequestInterface
{
    /**
     * The request cookies
     *
     * @var KHttpMessageParameters
     */
    protected $_cookies;

    /**
     * The request files
     *
     * @var KHttpMessageParameters
     */
    protected $_files;

    /**
     * Base url of the request.
     *
     * @var KHttpUrl
     */
    protected $_base_url;

    /**
     * Base path of the request.
     *
     * @var string
     */
    protected $_base_path;

    /**
     * Root url of the request.
     *
     * @var KHttpUrl
     */
    protected $_root;

    /**
     * Referrer of the request
     *
     * @var KHttpUrl
     */
    protected $_referrer;

    /**
     * The supported languages
     *
     * @var array
     */
    protected $_languages;

    /**
     * The supported charsets
     *
     * @var array
     */
    protected $_charsets;

    /**
     * A list of trusted proxies
     *
     * @var array
     */
    protected $_proxies;

    /**
     * A list of trusted origins
     *
     * @var array
     */
    protected $_origins;

    /**
     * The requested ranges
     *
     * @var array
     */
    protected $_ranges;

    /**
     * Constructor
     *
     * @param KObjectConfig $config  An optional ObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Set the trusted proxies
        $this->setProxies(KObjectConfig::unbox($config->proxies));

        //Set files parameters
        $this->setFiles($config->files);

        //Set cookie parameters
        $this->setCookies($config->cookies);

        //Set the base URL
        $this->setBaseUrl($config->base_url);

        //Set the base path
        $this->setBasePath($config->base_path);

        //Set document root for IIS
        if(!isset($_SERVER['DOCUMENT_ROOT']))
        {
            if(isset($_SERVER['SCRIPT_FILENAME'])) {
                $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
            }

            if(isset($_SERVER['PATH_TRANSLATED'])) {
                $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
            }
         }

        //Set the authorization
        if (!isset($_SERVER['PHP_AUTH_USER']))
        {
            /*
             * If you are running PHP as CGI. Apache does not pass HTTP Basic user/pass to PHP by default.
             * To fix this add these lines to your .htaccess file:
             *
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             */

            //When using PHP-FPM HTTP_AUTHORIZATION is called REDIRECT_HTTP_AUTHORIZATION
            if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }

            // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
            if (isset($_SERVER['HTTP_AUTHORIZATION']) && stripos($_SERVER['HTTP_AUTHORIZATION'], 'basic') === 0)
            {
                $exploded = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'] , 6)));
                if (count($exploded) == 2) {
                    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        //Set the headers
        $headers = array();
        foreach ($_SERVER as $key => $value)
        {
            if ($value && strpos($key, 'HTTP_') === 0)
            {
                // Cookies are handled using the $_COOKIE superglobal
                if (strpos($key, 'HTTP_COOKIE') === 0) {
                    continue;
                }

                $headers[substr($key, 5)] = $value;
            }
            elseif ($value && strpos($key, 'CONTENT_') === 0)
            {
                $name = substr($key, 8); // Content-
                $name = 'Content-' . (($name == 'MD5') ? $name : ucfirst(strtolower($name)));

                $headers[$name] = $value;
            }
        }

        $this->_headers->add($headers);

        //Set the version
        if (isset($_SERVER['SERVER_PROTOCOL']) && strpos($_SERVER['SERVER_PROTOCOL'], '1.0') !== false) {
            $this->setVersion('1.0');
        }

        //Set the request time
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $this->setTime($_SERVER['REQUEST_TIME_FLOAT']);
        }

        //Set request data
        if($this->getContentType() == 'application/x-www-form-urlencoded')
        {
            if (in_array($this->getMethod(), array('PUT', 'DELETE', 'PATCH')))
            {
                parse_str($this->getContent(), $data);
                $this->data->add($data);
            }
        }

        if($this->getContentType() == 'application/json')
        {
            if(in_array($this->getMethod(), array('POST', 'PUT', 'DELETE', 'PATCH')))
            {
                $data = array();

                if ($content = $this->getContent()) {
                    $data = json_decode($content, true);
                }

                $this->data->add($data);
            }
        }

        //Add the trusted origins
        $origins = KObjectConfig::unbox($config->origins) + array($this->getHost());
        foreach($origins as $origin) {
            $this->addOrigin($origin);
        }
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
            'base_url'  => '/',
            'base_path' => null,
            'url'       => null,
            'method'   => null,
            'query'   => $_GET,
            'data'    => $_POST,
            'cookies' => $_COOKIE,
            'files'   => $_FILES,
            'proxies' => array(),
            'origins' => array(),
        ));

        parent::_initialize($config);
    }

    /**
     * Sets a list of trusted proxies.
     *
     * You should only list the reverse proxies that you manage directly.
     *
     * @param array $proxies A list of trusted proxies
     * @return KDispatcherRequestInterface
     */
    public function setProxies(array $proxies)
    {
        $this->_proxies = $proxies;
        return $this;
    }

    /**
     * Gets the list of trusted proxies.
     *
     * @return array An array of trusted proxies.
     */
    public function getProxies()
    {
        return $this->_proxies;
    }

    /**
     * Set the request cookies
     *
     * @param  array $parameters
     * @return KDispatcherRequestInterface
     */
    public function setCookies($parameters)
    {
        $this->_cookies = $this->getObject('lib:http.message.parameters', array('parameters' => $parameters));
    }

    /**
     * Get the request cookies
     *
     * @return KHttpMessageParameters
     */
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
     * Set the request files
     *
     * @param  array $parameters
     * @return KDispatcherRequestInterface
     */
    public function setFiles($parameters)
    {
        $this->_files = $this->getObject('lib:http.message.parameters', array('parameters' => $parameters));
    }

    /**
     * Get the request files
     *
     * @return KHttpMessageParameters
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Returns current request method.
     *
     * @return  string|null Will return null if a overridde tries to set an unknown method
     */
    public function getMethod()
    {
        if(!isset($this->_method) && isset($_SERVER['REQUEST_METHOD']))
        {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if($method == 'POST')
            {
                if($this->_headers->has('X-Http-Method-Override')) {
                    $method = strtoupper($this->_headers->get('X-Http-Method-Override'));
                }

                if($this->data->has('_method')) {
                    $method = strtoupper($this->data->get('_method', 'alpha'));
                }

                if(!in_array($method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))) {
                    $method = null;
                }
            }

            $this->_method = $method;
        }

        return $this->_method;
    }

    /**
     * Get the POST or PUT raw content information
     *
     * The raw post data is not available with enctype="multipart/form-data".
     *
     * @return  string  The content data
     */
    public function getContent()
    {
        if (empty($this->_content) && $this->_headers->has('Content-Length') && $this->_headers->get('Content-Length') > 0)
        {
            $data = '';

            $input = fopen('php://input', 'r');
            while ($chunk = fread($input, 1024)) {
                $data .= $chunk;
            }

            fclose($input);

            $this->_content = $data;
        }

        return $this->_content;
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Returns the host name.
     *
     * This method can read the client host from the "X-Forwarded-Host" header when the request is proxied and the proxy
     * is trusted. The "X-Forwarded-Host" header must contain the client host name.
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.3
     *
     * @throws \UnexpectedValueException when the host name is invalid
     * @return string
     */
    public function getHost()
    {
        if($this->isProxied() && $this->_headers->has('X-Forwarded-Host'))
        {
            $host = $this->_headers->get('X-Forwarded-Host');
            $parts = explode(',', $host);
            $host  = $parts[count($parts) - 1];
        }
        else
        {
            if (!$host = $this->_headers->get('Host'))
            {
                if (!isset($_SERVER['SERVER_NAME'])) {
                    $host = $this->getAddress();
                } else {
                    $host = $_SERVER['SERVER_NAME'];
                }
            }
        }

        // Remove port number from host
        $host = preg_replace('/:\d+$/', '', $host);

        // Host is lowercase as per RFC 952/2181
        $host = trim(strtolower($host));

        // Make sure host does not contain forbidden characters (see RFC 952 and RFC 2181)
        if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
            throw new UnexpectedValueException('Invalid Host');
        }

        return $host;
    }

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header when the request is proxied .
     * The "X-Forwarded-Port" header must contain the client port. If the X-Forwarded-Port header is not present
     * return default ports based on the protocol used.
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.5
     *
     * @return string
     */
    public function getPort()
    {
        if ($this->isProxied())
        {
            if($this->_headers->has('X-Forwarded-Port')) {
                $port = $this->_headers->get('X-Forwarded-Port');
            } else {
                $port = $this->isSecure() ? '443' : '80';
            }
        }
        else $port = @$_SERVER['SERVER_PORT'];

        return $port;
    }

    /**
     * Return the Url of the request regardless of the server
     *
     * @return  KHttpUrl A HttpUrl object
     */
    public function getUrl()
    {
        if(!isset($this->_url))
        {
            //Scheme
            $scheme = $this->getScheme();

            //Host
            $host   = $this->getHost();

            /*
             * Since we are assigning the URI from the server variables, we first need to determine if we
             * are running on apache or IIS.  If PHP_SELF and REQUEST_URI are present, we will assume we
             * are running on apache.
             */
            if (!empty ($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI']))
            {
                //Prepend the protocol, and the http host to the URI string.
                $url = $scheme.'://'.$host . $_SERVER['REQUEST_URI'];
            }
            else
            {
                /*
                 * Since we do not have REQUEST_URI to work with, we will assume we are running on IIS
                 * and will therefore need to work some magic with the SCRIPT_NAME and QUERY_STRING
                 * environment variables.
                 */

                // IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable
                $url = $scheme.'://'.$host . $_SERVER['SCRIPT_NAME'];

                // If the query string exists append it to the URI string
                if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                    $url .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            // Sanitize the url since we can't trust the server var
            $url = $this->getObject('lib:filter.url')->sanitize($url);

            // Create the URI object
            $this->_url = $this->getObject('lib:http.url', array('url' => $url));

            //Set the url port
            $port = $this->getPort();

            if (($this->_url->scheme == 'http' && $port != 80) || ($this->_url->scheme == 'https' && $port != 443)) {
                $this->_url->port = $port;
            }
        }

        return $this->_url;
    }

    /**
     * Set the url for this request
     *
     * @param string|array  $url Part(s) of an URL in form of a string or associative array like parse_url() returns
     * @return KHttpRequest
     */
    public function setUrl($url)
    {
        if(!empty($url)) {
            $this->_url = $this->getObject('lib:http.url', array('url' => $url));
        }

        return $this;
    }

    /**
     * Returns the HTTP referrer.
     *
     * If a base64 encoded _referrer property exists in the request payload, it is used instead of the referrer.
     * 'referer' a commonly used misspelling word for 'referrer'
     * @link http://en.wikipedia.org/wiki/HTTP_referrer
     *
     * @param   boolean  $isTrusted Only allow trusted origins
     * @return  KHttpUrl|null  A HttpUrl object or NULL if no referrer could be found
     */
    public function getReferrer($isTrusted = true)
    {
        if(!isset($this->_referrer) && ($this->_headers->has('Referer') || $this->data->has('_referrer')))
        {
            if ($this->data->has('_referrer')) {
                $referrer = base64_decode($this->data->get('_referrer', 'base64'));
            } else {
                $referrer = $this->_headers->get('Referer');
            }

            $this->setReferrer($this->getObject('lib:filter.url')->sanitize($referrer));
        }

        $referrer = $this->_referrer;

        if(isset($referrer) && $isTrusted)
        {
            $trusted = false;
            $source  = $this->_referrer->getHost();

            foreach($this->getOrigins() as $target)
            {
                // Check if the source matches the target
                if($target == $source || '.'.$target === substr($source, -1 * (strlen($target)+1))) {
                    $trusted = true; break;
                }
            }

            if(!$trusted) {
                $referrer = null;
            }
        }

        return $referrer;
    }

    /**
     * Sets the referrer for the request
     *
     * @param  string|KHttpUrlInterface $referrer
     * @return $this
     */
    public function setReferrer($referrer)
    {
        if(!($referrer instanceof KHttpUrlInterface)) {
            $referrer = $this->getObject('lib:http.url', array('url' => $referrer));
        }

        $this->_referrer = $referrer;

        return $this;
    }

    /**
     * Add a trusted origin
     *
     * You should only add an origins that you trust
     *
     * @param string $origin A trusted origin
     * @return KDispatcherRequestInterface
     */
    public function addOrigin($origin)
    {
        if (strpos($origin, '://') !== false) {
            $origin = $this->getObject('lib:http.url', array('url' => $origin))->getHost();
        }

        if(!in_array($origin, (array) $this->_origins)) {
            $this->_origins[] = $origin;
        }

        return $this;
    }

    /**
     * Returns the HTTP origin header.
     *
     * @param   boolean  $isTrusted Only allow trusted origins
     * @return  KHttpUrl|null  A HttpUrl object or NULL if no origin header could be found
     */
    public function getOrigin($isTrusted = true)
    {
        $origin = null;

        if ($this->_headers->has('Origin'))
        {
            try
            {
                $origin = $this->getObject('lib:http.url', [
                    'url' => $this->getObject('lib:filter.url')->sanitize($this->_headers->get('Origin'))
                ]);

                if($isTrusted)
                {
                    $trusted = false;
                    $source  = $origin->getHost();

                    foreach($this->getOrigins() as $target)
                    {
                        // Check if the source matches the target
                        if($target == $source || '.'.$target === substr($source, -1 * (strlen($target)+1))) {
                            $trusted = true; break;
                        }
                    }

                    if(!$trusted) {
                        $origin = null;
                    }
                }
            }
            catch (UnexpectedValueException $e) {}
        }

        return $origin;
    }

    /**
     * Gets the list of trusted origins.
     *
     * @return array An array of trusted origins.
     */
    public function getOrigins()
    {
        return $this->_origins;
    }

    /**
     * Returns the agent who made the request
     *
     * @return string $_SERVER['HTTP_USER_AGENT'] or an empty string if it's not supplied in the request
     */
    public function getAgent()
    {
        return $this->_headers->get('User-Agent', '');
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client port from the "X-Forwarded-For" header when the request is proxied and the proxy
     * is trusted. The "X-Forwarded-For" header must contain the client address. The "X-Forwarded-For" header value is a
     * comma+space separated list of IP addresses, the left-most being the original client, and each successive proxy
     * that passed the request adding the IP address where it received the request from.
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
     *
     * @return string Client IP address or an empty string if it's not supplied in the request
     */
    public function getAddress()
    {
        if($this->isProxied() && $this->_headers->has('X-Forwarded-For'))
        {
            $addresses = $this->_headers->get('X-Forwarded-For');
            $addresses = array_map('trim', explode(',', $addresses));
            $addresses = array_reverse($addresses);

            $address   = $addresses[0];
        }
        else $address = $_SERVER['REMOTE_ADDR'];

        return $address;
    }

    /**
     * Returns the base URL from which this request is executed.
     *
     * @return  KHttpUrl  A HttpUrl object
     */
    public function getBaseUrl()
    {
        if(!$this->_base_url instanceof KHttpUrl)
        {
            $base = $this->getObject('lib:http.url', array('url' => $this->getUrl()->toString(KHttpUrl::AUTHORITY)));
            $base->setUrl($this->getBasePath());

            $this->_base_url = $base;
        }

        return $this->_base_url;
    }

    /**
     * Set the base URL for which the request is executed.
     *
     * @param string $url
     * @return KDispatcherRequestAbstract
     */
    public function setBaseUrl($url)
    {
        $this->_base_url = $url;
        return $this;
    }

    /**
     * Returns the base path of the request.
     *
     * @param   boolean $fqp If TRUE create a fully qualified path. Default FALSE.
     * @return  string
     */
    public function getBasePath($fqp = false)
    {
        if(!isset($this->_base_path))
        {
            // PHP-CGI on Apache with "cgi.fix_pathinfo = 0". We don't have user-supplied PATH_INFO in PHP_SELF
            if (strpos(PHP_SAPI, 'cgi') !== false && !ini_get('cgi.fix_pathinfo')  && !empty($_SERVER['REQUEST_URI'])) {
                $path = $_SERVER['PHP_SELF'];
            } else {
                $path = $_SERVER['SCRIPT_NAME'];
            }

            $this->_base_path = rtrim(dirname($path), '/\\');
        }

        $path = $fqp ? $_SERVER['DOCUMENT_ROOT'].$this->_base_path : $this->_base_path;

        // Sanitize the path since we can't trust the server var
        return $this->getObject('lib:filter.path')->sanitize($path);
    }

    /**
     * Set the base path for which the request is executed.
     *
     * @param string $path
     * @return KDispatcherRequestAbstract
     */
    public function setBasePath($path)
    {
        $this->_base_path = $path;
        return $this;
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages()
    {
        if (!isset($this->languages))
        {
            $this->_languages = array();

            if($this->_headers->has('Accept-Language'))
            {
                $languages = $this->getAccept();

                foreach (array_keys($languages) as $lang)
                {
                    if (strstr($lang, '-'))
                    {
                        $codes = explode('-', $lang);
                        if ($codes[0] == 'i')
                        {
                            // Language not listed in ISO 639 that are not variants
                            // of any listed language, which can be registered with the
                            // i-prefix, such as i-cherokee
                            if (count($codes) > 1) {
                                $lang = $codes[1];
                            }
                        }
                        else
                        {
                            for ($i = 0, $max = count($codes); $i < $max; $i++)
                            {
                                if ($i == 0) {
                                    $lang = strtolower($codes[0]);
                                } else {
                                    $lang .= '_'.strtoupper($codes[$i]);
                                }
                            }
                        }
                    }

                    $this->_languages[] = $lang;
                }
            }
        }

        return $this->_languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     */
    public function getCharsets()
    {
        if (!isset($this->_charsets))
        {
            $this->_charsets = array();

            if($this->_headers->has('Accept-Charset'))
            {
                $charsets = $this->getAccept();

                $this->_charsets = array_keys($charsets);
            }
        }

        return $this->_charsets;
    }

    /**
     * Gets the request ranges
     *
     * @link : http://tools.ietf.org/html/rfc2616#section-14.35
     *
     * @throws KHttpExceptionRangeNotSatisfied If the range info is not valid or if the start offset is large then the end offset
     * @return array List of request ranges
     */
    public function getRanges()
    {
        if(!isset($this->_ranges))
        {
            $this->_ranges = array();

            if($this->_headers->has('Range'))
            {
                $range  = $this->_headers->get('Range');

                if(!preg_match('/^bytes=((\d*-\d*,? ?)+)$/', $range)) {
                    throw new KHttpExceptionRangeNotSatisfied('Invalid range');
                }

                $ranges = explode(',', substr($range, 6));
                foreach ($ranges as $key => $range)
                {
                    $parts = explode('-', $range);
                    $first = $parts[0];
                    $last  = $parts[1];

                    $ranges[$key] = array('first' => $first, 'last' => $last);
                }

                $this->_ranges = $ranges;
            }
        }

        return $this->_ranges;
    }

    /**
     * Gets the etag
     *
     * @link https://tools.ietf.org/html/rfc7232#page-14
     *
     * @return string|null The entity tag, or null if the etag header doesn't exist
     */
    public function getETag()
    {
        if($result = $this->_headers->get('If-None-Match'))
        {
            //Remove the encoding from the etag
            //
            //RFC-7232 explicitly states that ETags should be content-coding aware
            $result = str_replace(['-gzip', '-br'], '', $result);
        }

        return $result;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client scheme from the "X-Forwarded-Proto" header when the request is proxied and the
     * proxy is trusted. The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.4
     *
     * @return  boolean
     */
    public function isSecure()
    {
        if ($this->isProxied() && $this->_headers->has('X-Forwarded-Proto')) {
            $scheme = $this->_headers->get('X-Forwarded-Proto');
        } else {
            $scheme = isset($_SERVER['HTTPS']) ? strtolower($_SERVER['HTTPS']) : 'http';
        }

        return in_array(strtolower($scheme), array('https', 'on', '1'));
    }

    /**
     * Checks whether the request is proxied or not.
     *
     * The request is considered to be proxied if the X-Forwarded-For is provided.
     *
     * If one or more trusted proxies are defined this method reads the proxy IP from the "X-Forwarded-By" header.
     * If no "X-Forwarded-By" header can be found, or the header IP address doesn't match the list of trusted proxies
     * the function will return false. The "X-Forwarded-By" header must contain the proxy IP address and, potentially,
     * a port number).
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#page-7
     *
     * @return  boolean Returns TRUE if the request is proxied (and trusted). FALSE otherwise.
     */
    public function isProxied()
    {
        if($this->_headers->has('X-Forwarded-For'))
        {
            if(!empty($this->_proxies))
            {
                if($this->_headers->has('X-Forwarded-By'))
                {
                    $ip      = $this->_headers->get('X-Forwarded-By');
                    $proxies = $this->getProxies();

                    //Validates the proxied IP-address against the list of trusted proxies.
                    foreach ($proxies as $proxy)
                    {
                        if (strpos($proxy, '/') !== false)
                        {
                            list($address, $netmask) = explode('/', $proxy, 2);

                            if ($netmask < 1 || $netmask > 32) {
                                return false;
                            }
                        }
                        else
                        {
                            $address = $proxy;
                            $netmask = 32;
                        }

                        if(substr_compare(sprintf('%032b', ip2long($ip)), sprintf('%032b', ip2long($address)), 0, $netmask) === 0) {
                            return true;
                        }
                    }
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Check if the request is downloadable or not.
     *
     * A request is downloading if one of the following conditions are met :
     *
     * 1. The request query contains a 'force-download' parameter
     * 2. The request accepts specifies either the application/force-download or application/octet-stream mime types
     *
     * @return bool Returns TRUE If the request is downloadable. FALSE otherwise.
     */
    public function isDownload()
    {
        $result = $this->query->has('force-download');

        if($this->headers->has('Accept'))
        {
            $types  = $this->getAccept();

            //Get the highest quality format
            $type = key($types);

            if(in_array($type, array('application/force-download', 'application/octet-stream'))) {
                return $result = true;
            }
        }

        return $result;
    }

    /**
     * Check if the request is streaming
     *
     * Responses that contain a Range header is considered to be streaming.
     * @link : http://tools.ietf.org/html/rfc2616#section-14.35
     *
     * @return bool
     */
    public function isStreaming()
    {
        return $this->_headers->has('Range');
    }

    /**
     * Implement a virtual 'headers', 'query' and 'data class property to return their respective objects.
     *
     * @param   string $name  The property name.
     * @return  string $value The property value.
     */
    public function __get($name)
    {
        if($name == 'cookies') {
            return $this->getCookies();
        }

        if($name == 'files') {
            return $this->getFiles();
        }

        return parent::__get($name);
    }



    /**
     * Deep clone of this instance
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();

        $this->_cookies = clone $this->_cookies;
        $this->_files   = clone $this->_files;
    }
}