<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Assigns and removes users from groups
 */
class ComKoowaControllerBehaviorRestrictable extends KControllerBehaviorAbstract implements KObjectMultiton
{
    protected $_component_map = ['docman' => 'DOCman'];

    protected $_actions;

    public function __construct(KObjectConfig $config)
    {   
        parent::__construct($config);

        $this->_actions = $config->actions->toArray();
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(['actions' => []]);

        parent::_initialize($config);
    }

    protected function _beforeRender(KControllerContextInterface $context)
    {
        $result = false;

        if (JFactory::getApplication()->isClient('administrator'))
        {
            $result = $this->isRestricted();

            if ($result) $this->_redirect($context);    
        }

        return !$result;

    }

    protected function _redirect(KControllerContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        $referrer = $request->getReferrer();

        $url = $referrer ?: $request->getSiteUrl();

        $identifier = $this->getMixer()->getIdentifier();

        $component = $identifier->getPackage();

        if (isset($this->_component_map[$component])) $component = $this->_component_map[$component];

        $response->setRedirect($url, $this->getObject('translator')->translate('license expiry', $component), KControllerResponseInterface::FLASH_WARNING);
    }

    public function getRestrictedActions()
    {
        return $this->_actions;
    }

    public function isRestrictedAction($action)
    {
        if (strpos($action, 'can') === 0) {
            $action = KStringInflector::underscore(str_replace('can', '', $action));
        }

        return in_array($action, $this->_actions);
    }

    public function isRestricted()
    {
        $result = true;

        try
        {
            $license = $this->getObject('license');

            $expiry = $license->getExpiry();
            
            if ($expiry == 0) {
                $result = false;
            } 
        }
        catch(Exception $e)
        {
            // Exceptions are handled as expired subs

            $result = false;
        }

        return $result;
    }
}