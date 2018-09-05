<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Ascii Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Filter
 */
class KFilterAscii extends KFilterAbstract implements KFilterTraversable
{
    /**
     * Validate a variable
     *
     * Returns true if the string only contains US-ASCII
     *
     * @param   mixed   $value Variable to be validated
     * @return  bool    True when the variable is valid
     */
    public function validate($value)
    {
        return (preg_match('/(?:[^\x00-\x7F])/', $value) !== 1);
    }

    /**
     * Transliterate all unicode characters to US-ASCII. The string must be well-formed UTF8
     *
     * @param   mixed   $value Variable to be sanitized
     * @return  mixed
     */
    public function sanitize($value)
    {
        if(class_exists('Transliterator'))
        {
            //Convert anything outside the ISO-8859-1 range to nearest ASCII
            $string = Transliterator::create('Any-Latin; [^a-ÿ] Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove;')
                ->transliterate($value);

            if ($string === false) {
                throw new UnexpectedValueException('Cannot transliterate string to ASCII');
            }
        }
        else
        {
            $string = htmlentities(utf8_decode($value), ENT_SUBSTITUTE);
            $string = preg_replace(
                array('/&szlig;/','/&(..)lig;/', '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
                array('ss',"$1","$1".'e',"$1"),
                $string);
        }

        return $string;
    }
}
