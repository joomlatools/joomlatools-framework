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
                $authenticator->setMixer($dispatcher);
                $authenticator->authenticateRequest($dispatcher->getContext());
            }
        }

        /*
         * Joomla 4: Router sets the results of the router in $_REQUEST and not in $_GET.
         *
         * So we hook into router postprocess and set the results in our request object
         */
        if (class_exists('JUri') && JFactory::getApplication()->isClient('site'))
        {
            if (version_compare(JVERSION, '4.0', '>='))
            {
                try
                {
                    $request = $this->getObject('request');
                    $router  = JFactory::getApplication()->getRouter();

                    $router->attachParseRule(function($router, &$uri) use($request) {
                        foreach ($uri->getQuery(true) as $key => $value)
                        {
                            if (!$request->query->has($key)) {
                                $request->query->set($key, $value);
                            }
                        }

                        if ($request->query->has('limitstart')) {
                            $request->query->offset = $request->query->limitstart;
                        }
                    }, \Joomla\CMS\Router\Router::PROCESS_AFTER);
                }
                catch (\Exception $e) {
                    if (JDEBUG) { throw $e; }
                }
            }
        }
    }

    /*
     * Joomla Compatibility
     *
     * For Joomla 3.x : Re-run the routing and add returned keys to the $_GET request. This is done because Joomla 3
     * sets the results of the router in $_REQUEST and not in $_GET
     *
     */
    public function onAfterApplicationRoute(KEventInterface $event)
    {
        $request = $this->getObject('request');

        if (class_exists('JUri') && JFactory::getApplication()->isClient('site'))
        {
            if (version_compare(JVERSION, '4.0', '<'))
            {
                try
                {
                    $uri    = clone JURI::getInstance();
                    $router = JFactory::getApplication()->getRouter();
                    $result = $router->parse($uri);

                    foreach ($result as $key => $value)
                    {
                        if (!$request->query->has($key)) {
                            $request->query->set($key, $value);
                        }
                    }
                }
                catch (\Exception $e) {
                    if (JDEBUG) { throw $e; }
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
     * Ensure a referrer is available for same origin requests by force setting the referrer policy
     *
     * If the referrer-policy is set to no-referrer, origin, or strict-origin override it and send
     * strict-origin-when-cross-origin instead.
     *
     * strict-origin-when-cross-origin: Send the origin, path, and querystring when performing a same-origin request,
     * only send the origin when the protocol security level stays the same while performing a cross-origin request
     * (HTTPS→HTTPS), and send no header to any less-secure destinations (HTTPS→HTTP).
     *
     * @param KEventInterface $event
     */
    public function onAfterApplicationRender(KEventInterface $event)
    {
        if(!headers_sent())
        {
            /**
             * Returns true if referrer-policy is set to any of  no-referrer, origin, or strict-origin
             *
             * @param array $headers
             * @return bool
             */
            $hasProblematicReferrerPolicy = function (array $headers) {
                foreach ($headers as $header) {
                    if (stripos(trim($header), 'referrer-policy:') === 0) {
                        $policy = trim(substr($header, strpos($header, ':')+1));

                        // Only these three is a problem for our case
                        return in_array($policy, ['no-referrer', 'origin', 'strict-origin']);
                    }
                }

                return false;
            };

            // Since header_register_callback can be overridden later on, we set the referrer policy once here as well
            if ($hasProblematicReferrerPolicy(headers_list())) {
                header('Referrer-Policy: strict-origin-when-cross-origin', true);
            }

            header_register_callback(function() use($hasProblematicReferrerPolicy) {
                if ($hasProblematicReferrerPolicy(headers_list())) {
                    header('Referrer-Policy: strict-origin-when-cross-origin', true);
                }
            });
        }
    }
}
