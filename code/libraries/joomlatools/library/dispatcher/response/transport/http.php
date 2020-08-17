<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Default Dispatcher Response Transport
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Response\Transport
 */
class KDispatcherResponseTransportHttp extends KDispatcherResponseTransportAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param 	KObjectConfig $config 	An optional ObjectConfig object with configuration options.
     * @return 	void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_LOW,
        ));

        parent::_initialize($config);
    }

    /**
     * Send HTTP headers
     *
     * @param KDispatcherResponseInterface $response
     * @throws \RuntimeException If the headers have already been sent
     * @return KDispatcherResponseTransportAbstract
     */
    public function sendHeaders(KDispatcherResponseInterface $response)
    {
        if(!headers_sent($file, $line))
        {
            //Send the status header
            header(sprintf('HTTP/%s %d %s', $response->getVersion(), $response->getStatusCode(), $response->getStatusMessage()));

            //Send the other headers
            $headers = explode("\r\n", trim((string) $response->getHeaders()));

            foreach ($headers as $header) {
                header($header, false);
            }
        }
        else throw new \RuntimeException(sprintf('Headers already send (output started at %s:%s', $file, $line));

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @param KDispatcherResponseInterface $response
     * @return KDispatcherResponseTransportAbstract
     */
    public function sendContent(KDispatcherResponseInterface $response)
    {
        //Make sure we do not have body content for 204, 205 and 305 status codes
        $codes = array(KHttpResponse::NO_CONTENT, KHttpResponse::NOT_MODIFIED, KHttpResponse::RESET_CONTENT);
        if (!in_array($response->getStatusCode(), $codes)) {
            echo $response->getStream()->toString();
        }

        return $this;
    }

    /**
     * Send HTTP response
     *
     * Prepares the Response before it is sent to the client. This method tweaks the headers to ensure that
     * it is compliant with RFC 2616 and calculates or modifies the cache-control header to a sensible and
     * conservative value
     *
     * @link http://tools.ietf.org/html/rfc2616
     *
     * @param KDispatcherResponseInterface $response
     * @return boolean  Returns true if the response has been send, otherwise FALSE
     */
    public function send(KDispatcherResponseInterface $response)
    {
        $request = $response->getRequest();

        //Remove location header if we are not redirecting and the status code is not 201
        if(!$response->isRedirect() && $response->getStatusCode() !== KHttpResponse::CREATED)
        {
            if($response->headers->has('Location')) {
                $response->headers->remove('Location');
            }
        }

        // IIS does not like it when you have a Location header in a non-redirect request
        // http://stackoverflow.com/questions/12074730/w7-pro-iis-7-5-overwrites-php-location-header-solved
        if ($response->headers->has('Location') && !$response->isRedirect())
        {
            $server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE');

            if ($server && strpos(strtolower($server), 'microsoft-iis') !== false) {
                $response->headers->remove('Location');
            }
        }

        //Add file related information if we are serving a file
        if($response->isDownloadable())
        {
            //Make sure the output buffers are cleared
            $level = ob_get_level();
            while($level > 0) {
                ob_end_clean();
                $level--;
            }

            //Last-Modified header
            if($time = $response->getStream()->getTime(KFilesystemStreamInterface::TIME_MODIFIED)) {
                $response->setLastModified($time);
            };

            $user_agent = $response->getRequest()->getAgent();

            if (!$response->headers->has('Content-Disposition'))
            {
                if ($response->headers->has('X-Content-Disposition-Filename'))
                {
                    $filename = $response->headers->get('X-Content-Disposition-Filename');
                    $response->headers->remove('X-Content-Disposition-Filename');
                }
                else
                {
                    // basename does not work if the string starts with a UTF character
                    $filename   = \Koowa\basename($response->getStream()->getPath());
                }

                // Android cuts file names after #
                if (stripos($user_agent, 'Android')) {
                    $filename = str_replace('#', '_', $filename);
                }

                $directives = array('filename' => '"'.$filename.'"');

                // IE accepts percent encoded file names as the filename value
                // Other browsers (except Safari) use filename* header starting with UTF-8''
                $encoded_name = rawurlencode($filename);

                if($encoded_name !== $filename)
                {
                    $webkit13OrHigher = preg_match('#(.+)AppleWebKit(.+)Version\/([2-9]+0|1[3-9])(.+)#', $user_agent);

                    if (preg_match('/(?:\b(MS)?IE\s+|\bTrident\/7\.0;.*\s+rv:)(\d+)/i', $user_agent)) {
                        $directives['filename'] = '"'.$encoded_name.'"';
                    }
                    elseif ($webkit13OrHigher || !stripos($user_agent, 'AppleWebkit')) {
                        $directives['filename*'] = 'UTF-8\'\''.$encoded_name;
                    }
                }

                $disposition = $response->isAttachable() ? 'attachment' : 'inline';

                //Disposition header
                $response->headers->set('Content-Disposition', [$disposition => $directives]);
            }

            //Force a download by the browser by setting the disposition to 'attachment'.
            if($response->isAttachable()) {
                $response->setContentType('application/octet-stream');
            }

            //Add Content-Length if not present
            if(!$response->headers->has('Content-Length')) {
                $response->headers->set('Content-Length', $response->getStream()->getSize());
            }
        }

        //Remove Content-Length for transfer encoded responses that do not contain a content range
        if ($response->headers->has('Transfer-Encoding')) {
            $response->headers->remove('Content-Length');
        }

        //Set Content-Type if not present
        if(!$response->headers->has('Content-Type')) {
            $response->setContentType($request->getFormat(true));
        }

        //Set cache-control header to most conservative value.
        if (!$request->isCacheable()) {
            $response->headers->set('Cache-Control', array('private', 'no-cache', 'no-store'));
        }

        //Validate the response
        if($response->isNotModified()) {
            $response->setStatus(KHttpResponse::NOT_MODIFIED);
        }

        //Modifies the response so that it conforms to the rules defined for a 304 status code.
        if($response->getStatusCode() == KHttpResponse::NOT_MODIFIED)
        {
            $headers = array(
                'Allow',
                'Age',
                'Content-Encoding',
                'Content-Language',
                'Content-Length',
                'Content-Type',
            );

            //Remove headers that MUST NOT be included with 304 Not Modified responses
            foreach ($headers as $header) {
                $response->headers->remove($header);
            }

            //Reset the date if the response has been succesfully validated
            $response->setDate(new DateTime('now'));
        }

        // Prevent caching: Cache-control needs to be empty for IE on SSL.
        // See: http://support.microsoft.com/default.aspx?scid=KB;EN-US;q316431
        if ($request->isSecure() && preg_match('#(?:MSIE |Internet Explorer/)(?:[0-9.]+)#', $request->getAgent())) {
            $response->headers->set('Cache-Control', '');
        }

        //Add request time in seconds
        if($start = $response->getRequest()->getTime())
        {
            $time  = (microtime(true) - $start) * 1000;
            $response->headers->set('Server-Timing', 'tot;desc="Total";dur='.(int) $time);
        }

        //Send headers and content
        $this->sendHeaders($response)
             ->sendContent($response);

        return parent::send($response);
    }
}
