<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Application Event Subscriber
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaEventSubscriberApplication extends KEventSubscriberAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => KEvent::PRIORITY_HIGH
        ));

        parent::_initialize($config);
    }

    /**
     * Log user in from the JWT token in the request if possible
     *
     * onAfterInitialise is used here to make sure that Joomla doesn't display error messages for menu items
     * with registered and above access levels.
     */
    public function onAfterApplicationInitialise(KEventInterface $event)
    {
        if(JFactory::getUser()->guest)
        {
            $authenticator = $this->getObject('com:koowa.dispatcher.authenticator.jwt');

            if ($authenticator->getAuthToken())
            {
                $dispatcher = $this->getObject('com:koowa.dispatcher.http');
                $authenticator->authenticateRequest($dispatcher->getContext());
            }
        }
    }

    /*
     * Joomla Compatibility
     *
     * For Joomla 3.x : Re-run the routing and add returned keys to the $_GET request. This is done because Joomla 3
     * sets the results of the router in $_REQUEST and not in $_GET
     */
    public function onAfterApplicationRoute(KEventInterface $event)
    {
        $request = $this->getObject('request');

        $app = JFactory::getApplication();
        if ($app->isSite())
        {
            $uri     = clone JURI::getInstance();

            $router = JFactory::getApplication()->getRouter();
            $result = $router->parse($uri);

            foreach ($result as $key => $value)
            {
                if (!$request->query->has($key)) {
                    $request->query->set($key, $value);
                }
            }
        }

        if ($request->query->has('limitstart')) {
            $request->query->offset = $request->query->limitstart;
        }
    }

    /*
     * Joomla Compatibility
     *
     * For Joomla 2.5 and 3.x : Handle session messages if they have not been handled by Koowa for example after a
     * redirect to a none Koowa component.
     */
    public function onAfterApplicationDispatch(KEventInterface $event)
    {
        $messages = $this->getObject('user')->getSession()->getContainer('message')->all();

        foreach($messages as $type => $group)
        {
            if ($type === 'success') {
                $type = 'message';
            }

            foreach($group as $message) {
                JFactory::getApplication()->enqueueMessage($message, $type);
            }
        }
    }

    /**
     * Adds application response time and memory usage to Chrome Inspector with ChromeLogger extension
     *
     * See: https://chrome.google.com/webstore/detail/chrome-logger/noaneddfkdjfnfdakjjmocngnfkfehhd
     */
    public function onAfterApplicationRender(KEventInterface $event)
    {
        if(!headers_sent())
        {
            /*
             * Ensure a referrer is available for same origin requests by force setting the referrer policy
             *
             * Send the origin, path, and querystring when performing a same-origin request, only send the
             * origin when the protocol security level stays the same while performing a cross-origin request
             * (HTTPS→HTTPS), and send no header to any less-secure destinations (HTTPS→HTTP).
             */
            if($this->getObject('user')->isAuthentic() && $this->getObject('response')->getFormat() == 'html')
            {
                header_register_callback(function() {
                    header('Referrer-Policy: strict-origin-when-cross-origin', true);
                });
            }

            if (JDEBUG)
            {
                $buffer = JProfiler::getInstance('Application')->getBuffer();
                if ($buffer)
                {
                    $data = strip_tags(end($buffer));
                    $row = array(array($data), null, 'info');

                    $header = array(
                        'version' => '4.1.0',
                        'columns' => array('log', 'backtrace', 'type'),
                        'rows'    => array($row)
                    );

                    header('X-ChromeLogger-Data: ' . base64_encode(utf8_encode(json_encode($header))));
                }
            }
        }
    }
}