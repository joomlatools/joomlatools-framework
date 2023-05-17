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
class ComKoowaControllerBehaviorRestrictable extends KControllerBehaviorAbstract implements KObjectSingleton
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

            $license = $this->_getLicense();

            if ($result)
            {
                if ($subscription = $license->getSubscription($this->_getComponent(true), false))
                {
                    $past = ($subscription['end'] - time())/86400;

                    if ($past <= 7)
                    {
                        $message = $this->getObject('translator')->translate('license recent expiry', ['component' => $this->_getComponent()]);

                        $context->getResponse()->addMessage($message,KControllerResponseInterface::FLASH_WARNING);
                    }
                    else $this->_redirect($context);   
                }
            }
            elseif ($subscription = $license->getSubscription($this->_getComponent(true)))
            {
                if (isset($subscription['cancelled']) && $subscription['cancelled'])
                {
                    $remaining = ($subscription['end'] - time())/604800;

                    if ($remaining <= 4) // A month before
                    {
                        $message = $this->getObject('translator')->translate('subscription cancelled', ['component' => $this->_getComponent()]);

                        $context->getResponse()->addMessage($message,KControllerResponseInterface::FLASH_WARNING);
                    }                        
                }
            }
        }

        return !$result;
    }

    protected function _getComponent($raw = false)
    {
        $identifier = $this->getMixer()->getIdentifier();

        $component = $identifier->getPackage();

        if (!$raw) {
            if (isset($this->_component_map[$component])) $component = $this->_component_map[$component];
        }

        return $component;
    }

    protected function _redirect(KControllerContextInterface $context)
    {
        $request  = $context->getRequest();
        $response = $context->getResponse();

        $config = $this->getConfig();

        if (!$config->redirect_url)
        {
            $referrer = $request->getReferrer();

            $url = $referrer ?: $request->getSiteUrl();
        }
        else $url = $config->redirect_url;

        $response->setRedirect($url, $this->getObject('translator')->translate('license expiry', ['component' => $this->_getComponent()]), KControllerResponseInterface::FLASH_ERROR);
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

    protected function _getLicense()
    {
        return $this->getObject('license');
    }

    public function isRestricted()
    {
        $result = true;

        try
        {
            $license = $this->_getLicense();
            
            $result = !$license->hasFeature($this->_getComponent());
        }
        catch(Exception $e)
        {
            // Exceptions are handled as expired subs

            $result = false;
        }

        if ($this->_isLocal()) $result = false;

        return $result;
    }

    protected function _isLocal()
    {
        static $local_hosts = array('localhost', '127.0.0.1', '::1');

        $url  = $this->getObject('request')->getUrl();
        $host = $url->host;

        if (in_array($host, $local_hosts)) {
            return true;
        }

        // Returns true if host is an IP address
        if (ip2long($host))
        {
            return (filter_var($host, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 |
                    FILTER_FLAG_IPV6 |
                    FILTER_FLAG_NO_PRIV_RANGE |
                    FILTER_FLAG_NO_RES_RANGE) === false);
        }
        else
        {
            // If no TLD is present, it's definitely local
            if (strpos($host, '.') === false) {
                return true;
            }

            return preg_match('/(?:\.)(local|localhost|test|example|invalid|dev|box|intern|internal)$/', $host) === 1;
        }
    }
}