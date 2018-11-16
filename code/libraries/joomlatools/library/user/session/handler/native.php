<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Native Session Handler
 *
 * It uses the default registered PHP session handler, whatever that might be
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\User\Session\Handler
 * @link    http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class KUserSessionHandlerNative extends KUserSessionHandlerAbstract
{
    /**
     * Do nothing since we are going to depend on the current PHP session handler
     */
    public function register()
    {
        static::$_registered = $this;
    }
}