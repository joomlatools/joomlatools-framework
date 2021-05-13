<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Unauthorized Event Subscriber
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Event\Subscriber
 */
class ComKoowaEventSubscriberUnauthorized extends KEventSubscriberAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => KEvent::PRIORITY_HIGHEST
        ));

        parent::_initialize($config);
    }

    public function onException(KEvent $event)
    {
        $exception = $event->exception;

        /**
         * Redirect user to login screen
         *
         * If a user does not have access to the entity and they are not logged in, they will be redirected to the login.
         */
        if($exception instanceof KHttpExceptionUnauthorized)
        {
            $request     = $this->getObject('request');
            $response    = $this->getObject('response');

            if ($request->getFormat() == 'html' && $request->isSafe())
            {
                $message = $this->getObject('translator')->translate('You are not authorized to access this resource. Please login and try again.');

                if($this->getObject('joomla')->isSite()) {
                    $url = $this->getObject('joomla')->route('index.php?option=com_users&view=login&return='.base64_encode((string) $request->getUrl()), false);
                } else {
                    $url = $this->getObject('joomla')->route('index.php', false);
                }

                $response->setRedirect($url, $message, 'error');
                $response->send();

                $event->stopPropagation();
            }
        }

        /**
         * Handles 404 errors gracefully after log outs
         *
         * If a user does not have access to the entity after logging out, they will be redirected to the homepage.
         */
        if($exception instanceof KHttpExceptionNotFound && $this->getObject('joomla')->isSite())
        {
            $hash = $this->getObject('joomla')->applicationHelper->getHash('PlgSystemLogout');

            $app = $this->getObject('joomla')->app;
            if ($app->input->cookie->getString($hash, null)) // just logged out
            {
                $app->enqueueMessage(JText::_('PLG_SYSTEM_LOGOUT_REDIRECT'));
                $app->redirect('index.php');

                $event->stopPropagation();
            }
        }
    }
}