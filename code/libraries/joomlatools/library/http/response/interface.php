<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Http Response Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Http\Response
 */
interface KHttpResponseInterface extends KHttpMessageInterface
{
    /**
     * Set HTTP status code and (optionally) message
     *
     * @link http://tools.ietf.org/html/rfc2616#section-6.1.1
     *
     * @param  integer $code
     * @param  string $message
     * @throws \InvalidArgumentException
     * @return KHttpResponse
     */
    public function setStatus($code, $message = null);

    /**
     * Retrieve HTTP status code
     *
     * @link http://tools.ietf.org/html/rfc2616#section-6.1.1
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Get the http header status message based on a status code
     *
     * @return string The http status message
     */
    public function getStatusMessage();

    /**
     * Sets the response content type
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.17
     *
     * @param string $type Content type
     * @return KHttpResponseInterface
     */
    public function setContentType($type);

    /**
     * Retrieves the response content type
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.17
     *
     * @return string Character set
     */
    public function getContentType();

    /**
     * Returns the Date header as a DateTime instance.
     *
     * @see http://tools.ietf.org/html/rfc2616#section-14.18
     *
     * @return DateTime A \DateTime instance
     * @throws RuntimeException When the header is not parseable
     */
    public function getDate();

    /**
     * Sets the Date header.
     *
     * @see http://tools.ietf.org/html/rfc2616#section-14.18
     *
     * @param  DateTime $date A \DateTime instance
     * @return KHttpResponseInterface
     */
    public function setDate(DateTime $date);

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.29
     *
     * @return DateTime A DateTime instance
     */
    public function getLastModified();

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * If passed a null value, it removes the header.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.29
     *
     * @param  DateTime $date A \DateTime instance
     * @return KHttpResponseInterface
     */
    public function setLastModified(DateTime $date = null);

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.19
     *
     * @return string The ETag HTTP header
     */
    public function getEtag();

    /**
     * Sets the ETag value.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.19
     *
     * @param string $etag The ETag unique identifier
     * @param Boolean $weak Whether you want a weak ETag or not
     * @return KHttpResponseInterface
     */
    public function setEtag($etag = null, $weak = false);

    /**
     * Returns the age of the response.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.6
     *
     * @return integer The age of the response in seconds
     */
    public function getAge();

    /**
     * Set the age of the response.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.6
     * @param integer $age The age of the response in seconds
     * @return KHttpResponseInterface
     */
    public function setAge($age);

    /**
     * Set the max age
     *
     * This directive specifies the maximum time in seconds that the fetched response is allowed to be reused from
     * the time of the request. For example, "max-age=60" indicates that the response can be cached and reused for
     * the next 60 seconds.
     *
     * @link https://tools.ietf.org/html/rfc2616#section-14.9.3
     * @param integer $max_age       The number of seconds after which the response should no longer be considered fresh.
     * @param integer $shared_max_age The number of seconds after which the response should no longer be considered fresh by shared caches.
     * @return KHttpResponse
     */
    public function setMaxAge($max_age, $shared_max_age = null);

    /**
     * Get the max age
     *
     * Returns the number of seconds after the time specified in the response's Date header when the response should no
     * longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive. It returns null when no maximum age can be
     * established.
     *
     * @link https://tools.ietf.org/html/rfc2616#section-14.9.3
     * @return integer Number of seconds
     */
    public function getMaxAge();

    /**
     * Get the cache control
     *
     * @link https://tools.ietf.org/html/rfc2616#page-108
     * @return array
     */
    public function getCacheControl();

    /**
     * Is the response invalid
     *
     * @return bool
     */
    public function isInvalid();

    /**
     * Check if an http status code is an error
     *
     * @return boolean TRUE if the status code is an error code
     */
    public function isError();

    /**
     * Do we have a redirect
     *
     * @return bool
     */
    public function isRedirect();

    /**
     * Was the response successful
     *
     * @return bool
     */
    public function isSuccess();

    /**
     * Returns true if the response includes headers that can be used to validate the response with the origin
     * server using a conditional GET request.
     *
     * @return Boolean true if the response is validateable, false otherwise
     */
    public function isValidateable();

    /**
     * Returns true if the response is worth caching under any circumstance.
     *
     * Responses with that are stale (Expired) or without cache validation (Last-Modified, ETag) headers are
     * considered uncacheable.
     *
     * @link http://tools.ietf.org/html/rfc2616#section-14.9.1
     *
     * @return Boolean true if the response is worth caching, false otherwise
     */
    public function isCacheable();

    /**
     * Returns true if the response is "stale".
     *
     * When the responses is stale, the response may not be served from cache without first re-validating with
     * the origin.
     *
     * @return Boolean true if the response is fresh, false otherwise
     */
    public function isStale();

    /**
     * Return true of the response has not been modified
     *
     * @return Boolean true if the response is not modified
     */
    public function isNotModified();
}