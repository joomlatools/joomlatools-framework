<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * MD5 Filter
 *
 * Validates or sanitizes an md5 hash (32 chars [a-f0-9])
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Filter
 */
class KFilterMd5 extends KFilterAbstract implements KFilterTraversable
{
    /**
     * Validate a value
     *
     * @param   mixed  $value Variable to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        $value = trim($value);
        $pattern = '/^[a-f0-9]{32}$/';
        return (is_string($value) && preg_match($pattern, $value) == 1);
    }

    /**
     * Sanitize a value
     *
     * @param   mixed  $value Variable to be sanitized
     * @return  string
     */
    public function sanitize($value)
    {
        $value      = trim(strtolower($value));
        $pattern    = '/[^a-f0-9]*/';
        return substr(preg_replace($pattern, '', $value), 0, 32);
    }
}
