<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Redirect Dispatcher Response Transport
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Response\Transport
 */
class KDispatcherResponseTransportRedirect extends KDispatcherResponseTransportHttp
{
    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config  An optional KObjectConfig object with configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_HIGH,
        ));

        parent::_initialize($config);
    }

    /**
     * Send HTTP response
     *
     * If this is a redirect response, send the response and stop the transport handler chain.
     *
     * @link: https://en.wikipedia.org/wiki/Meta_refresh
     *
     * @param KDispatcherResponseInterface $response
     * @return boolean
     */
    public function send(KDispatcherResponseInterface $response)
    {
        if($response->isRedirect())
        {
            $session = $response->getUser()->getSession();

            //Set the messages into the session
            $messages = $response->getMessages();
            if(count($messages))
            {
                //Auto start the session if it's not active.
                if(!$session->isActive()) {
                    $session->start();
                }

                $session->getContainer('message')->add($messages);
            }

            if($response->getRequest()->getFormat() == 'html')
            {
                //Set the redirect into the response
                $response->setContent(sprintf(
                    '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <noscript>
                                <meta http-equiv="refresh" content="1;url=%1$s" />
                            </noscript>
                            <title>Redirecting to %1$s</title>
                        </head>
                        <body onload="window.location = \'%1$s\'">
                            Redirecting to <a href="%1$s">%1$s</a>.
                        </body>
                    </html>'
                    , htmlspecialchars($response->headers->get('Location'), ENT_QUOTES, 'UTF-8')
                ), 'text/html');
            }

            return parent::send($response);
        }
    }
}