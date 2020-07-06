<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Dispatcher Context
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Dispatcher\Context
 */
class KDispatcherContext extends KControllerContext implements KDispatcherContextInterface
{
    /**
     * The request has been successfully authenticated
     *
     * @return Boolean
     */
    public function isAuthentic()
    {
        return (bool) KObjectConfig::get('authentic', $this->getUser()->isAuthentic(true));
    }

    /**
     * Sets the request as authenticated
     *
     * @return $this
     */
    public function setAuthentic()
    {
        KObjectConfig::set('authentic', true);
        return $this;
    }
}