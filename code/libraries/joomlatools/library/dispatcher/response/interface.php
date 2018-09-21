<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher Response Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Response
 */
interface KDispatcherResponseInterface extends KControllerResponseInterface
{
    /**
     * Send the response
     *
     * @param bool $terminate Whether to terminate the request by flushing it or not, defaults to TRUE
     * @return boolean  Returns true if the response has been send, otherwise FALSE
     */
    public function send($terminate = true);

    /**
     * Flush the output buffer and terminate request
     *
     * @return void
     */
    public function terminate();

    /**
     * Sets the response content using a stream
     *
     * @param KFilesystemStreamInterface $stream  The stream object
     * @return KDispatcherResponseInterface
     */
    public function setStream(KFilesystemStreamInterface $stream);

    /**
     * Get the stream resource
     *
     * @return KFilesystemStreamInterface
     */
    public function getStream();

    /**
     * Get a transport handler by identifier
     *
     * @param   mixed    $transport    An object that implements ObjectInterface, ObjectIdentifier object
     *                                 or valid identifier string
     * @param   array    $config    An optional associative array of configuration settings
     * @return KDispatcherResponseInterface
     */
    public function getTransport($transport, $config = array());

    /**
     * Attach a transport handler
     *
     * @param   mixed  $transport An object that implements ObjectInterface, ObjectIdentifier object
     *                            or valid identifier string
     * @param   array $config  An optional associative array of configuration settings
     * @return KDispatcherResponseInterface
     */
    public function attachTransport($transport, $config = array());
}