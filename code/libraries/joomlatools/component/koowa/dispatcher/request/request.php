<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher Request
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Dispatcher\Request
 */
final class ComKoowaDispatcherRequest extends KDispatcherRequest
{
    /**
     * Returns the site URL from which this request is executed.
     *
     * @return  KHttpUrl  A HttpUrl object
     */
    public function getSiteUrl()
    {
        $url = clone $this->getBaseUrl();

        if(class_exists('JUri'))
        {
            if(JFactory::getApplication()->getName() == 'administrator')
            {
                // Replace the application name only once since it's possible that
                // we can run from http://localhost/administrator/administrator
                $i    = 1;
                $path = str_ireplace('/administrator', '', $url->getPath(), $i);
                $url->setPath($path);
            }
        }

        return $url;
    }

    /**
     * Forces format to "rss" if it comes in as "feed" per Joomla conventions if SEF suffixes are enabled
     *
     * {@inheritdoc}
     */
    public function setFormat($format)
    {
        if(class_exists('JUri'))
        {
            if (JFactory::getConfig()->get('sef_suffix') && $format === 'feed') {
                $format = 'rss';
            }
        }

        return parent::setFormat($format);
    }

    /**
     * If the current Joomla URI is on https or PHP is on a secure connection always return 443 instead of 80
     *
     * {@inheritdoc}
     */
    public function getPort()
    {
        $port = parent::getPort();

        if(class_exists('JUri'))
        {
              if (JUri::getInstance()->isSsl() || ($this->isSecure() && in_array($port, ['80', '8080']))) {
                  $port = '443';
              }
        }

        return $port;
    }

    /**
     * If the current Joomla URI is on https always return true
     *
     * {@inheritdoc}
     */
    public function isSecure()
    {
        if(class_exists('JUri')) {
            $result =  JUri::getInstance()->isSsl();
        } else {
            $result = false;
        }

        return $result ? true : parent::isSecure();
    }

    /**
     * Checks whether the request is proxied or not.
     *
     * Joomla doesn't care if the X-Forwarded-By header is in a trusted list and proxies the request anyway.
     * In return, some Joomla servers are configured to return as X-Forwarded-Proto but they are missing X-Forwarded-By.
     *
     * So we are turning off the checks here to run in sync with Joomla.
     *
     * @return bool
     */
    public function isProxied()
    {
        if(class_exists('JUri')) {
            $result = true;
        } else {
            $result = parent::isProxied();
        }

        return $result;
    }
}