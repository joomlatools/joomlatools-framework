<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Exception Handler
 *
 * Setup error handler for Joomla context.
 *
 * 1. KOOWA_DEBUG enabled
 *
 * If KOOWA_DEBUG is enabled assume we are in local development mode
 *    - error types   : TYPE_ALL which will trigger an exception for : exceptions, errors and failures
 *    - error levels  : ERROR_DEVELOPMENT (E_ALL | E_STRICT | ~E_DEPRECATED)
 *
 * 2. JDEBUG enabled
 *
 * If JDEBUG debug is enabled assume we are in none local debug mode
 *    - error types   : TYPE_ALL which will trigger an exception for : exceptions, errors and failures
 *    - error levels  : E_ERROR and E_PARSE
 *
 * 3. Joomla default
 *
 * Do not try to trigger errors automatically. Exception handling is still required to be able to recover
 * from specific exceptions gracefully, like a 404 or 403 exception.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Exception
 */
final class ComKoowaExceptionHandler extends KExceptionHandler
{
    /**
     * Initializes the default configuration for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        if(Koowa::isDebug())
        {
            $config->append([
                'exception_type'  => self::TYPE_ALL,
                'error_reporting' => self::ERROR_DEVELOPMENT
            ]);
        }
        elseif (JDEBUG)
        {
            $config->append([
                'exception_type'  => self::TYPE_ALL,
                'error_reporting' => E_ERROR | E_PARSE
            ]);
        }
        else $config->append(['exception_type' => self::TYPE_EXCEPTION]);

        parent::_initialize($config);
    }
}