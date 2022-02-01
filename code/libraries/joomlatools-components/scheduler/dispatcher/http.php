<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-scheduler for the canonical source repository
 */

/**
 * Schedulable behavior
 *
 * @author Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Scheduler
 */
class ComSchedulerDispatcherHttp extends KDispatcherAbstract
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'controller' => 'com:scheduler.controller.dispatcher',
            'response'   => 'com:koowa.dispatcher.response',
            'request'    => 'com:koowa.dispatcher.request',
            'user'       => 'com:koowa.user',
            'debug'      => KClassLoader::getInstance()->isDebug()
        ));

        parent::_initialize($config);
    }

    protected function _actionDispatch(KDispatcherContextInterface $context)
    {
        // Ensure the URL is not indexed ("none" equals "noindex, nofollow")
        $context->getResponse()->getHeaders()->set('X-Robots-Tag', 'none');

        $job_dispatcher = $this->getController();

        $context = $job_dispatcher->getContext();

        $job_dispatcher->synchronize($context);

        $result  = null;
        $can_run = function($result, $context) use ($job_dispatcher) {
            static $i = 0, $time = 0.0;
            if ($i == 0 ||
                ($i < 5
                    && $job_dispatcher->getNextJob()
                    && ($result === ComSchedulerJobInterface::JOB_SKIP || $time < 7.5))
            ) {
                $i++;
                $time += $context->getJobDuration();

                $context->log(sprintf('Current total time %f', $time));

                return true;
            }

            return false;
        };

        while ($can_run($result, $context)) {
            $result = $job_dispatcher->dispatch($context);
        }

        $response = array(
            'continue' => (bool) $job_dispatcher->getNextJob(),
            'sleep_until' => $context->sleep_until,
            'logs'     => $this->getConfig()->debug ? $context->getLogs() : array()
        );

        $context->request->setFormat('json');
        $context->response->setContent(json_encode($response), 'application/json');
        $context->response->headers->set('Cache-Control', 'no-cache');

        $this->send();
    }
}