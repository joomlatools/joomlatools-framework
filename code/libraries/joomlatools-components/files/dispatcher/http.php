<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Dispatcher
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesDispatcherHttp extends ComKoowaDispatcherHttp
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        //Render an exception before sending the response
        $this->getObject('event.publisher')->addListener('onException', array($this, 'renderError'));
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'limit' => array(
                'max' => 2000 // Used in tree view
            )
        ));

        parent::_initialize($config);
    }

    /**
     * Plupload do not pass the error to our application if the status code is not 200
     *
     * @param mixed $event
     * @return bool
     * @throws Exception
     */
    public function renderError($event)
    {
    	if ($this->getRequest()->getFormat() == 'json')
        {
            $exception = $event instanceof KEvent ? $event->exception : $event;
    		$response = new stdClass;
    		$response->status = false;
    		$response->error  = $exception->getMessage();
    		$response->code   = $exception->getCode();

    		$status_code = $this->getRequest()->query->plupload ? 200 : ($exception->getCode() && $exception->getCode() <= 505 ? $exception->getCode() : 500);

            $this->getResponse()
                ->setStatus($status_code)
                ->setContent(json_encode($response), 'application/json')
                ->send();

            return false;
    	}
    }

    // FIXME: this is here because forwarded dispatchers still render results
    protected function _actionSend(KDispatcherContextInterface $context)
    {
        if (!$context->getRequest()->isGet() || $context->getResponse()->getContentType() !== 'text/html') {
            return parent::_actionSend($context);
        }
    }
}
