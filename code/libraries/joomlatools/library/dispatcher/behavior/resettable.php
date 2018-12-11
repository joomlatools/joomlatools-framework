<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Resettable Dispatcher Behavior - Post, Redirect, Get
 *
 * When a browser sends a POST request (e.g. after submitting a form), the browser will try to protect them from sending
 * the POST again, breaking the back button, causing browser warnings and pop-ups, and sometimes re-posting the form.
 *
 * Instead, when receiving a POST request with content type application/x-www-form-urlencoded and a valid referrer reset
 * the browser by redirecting it through a GET request to the referrer.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Behavior
 */
class KDispatcherBehaviorResettable extends KControllerBehaviorAbstract
{
    /**
     * Check if the behavior is supported
     *
     * @return  boolean  True on success, false otherwise
     */
    public function isSupported()
    {
        return $this->getMixer()->getRequest()->isFormSubmit();
    }

    /**
     * Force a GET after POST using the referrer
     *
     * Redirect if the controller has a returned a 2xx status code.
     *
     * @param 	KDispatcherContextInterface $context The active command context
     * @return 	void
     */
    protected function _beforeSend(KDispatcherContextInterface $context)
    {
        $response = $context->response;
        $request  = $context->request;

        if($response->isSuccess() && $referrer = $request->getReferrer()) {
            $response->setRedirect($referrer);
        }
    }
}
