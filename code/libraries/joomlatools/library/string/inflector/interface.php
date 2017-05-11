<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * String Inflector Interface
 *
 * Inflector to pluralize and singularize English nouns.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\String\Inflector
 */
interface KStringInflectorInterface
{
    /**
     * Singular English word to plural.
     *
     * @param   string $word Word to pluralize
     * @return  string Plural noun
     */
    public static function pluralize($word);

    /**
     * Plural English word to singular.
     *
     * @param   string $word Word to singularize.
     * @return  string Singular noun
     */
    public static function singularize($word);

    /**
     * Convert a word to "CamelCased"
     *
     * Converts a word like "foo_bar" or "foo bar" to "FooBar".
     *
     * @param   string  $word    Word to convert to camel case
     * @return  string  UpperCamelCasedWord
     */
    public static function camelize($word);

    /**
     * Convert a word "into_it_s_underscored_version"
     *
     * Convert any "OrdinaryWord" or "ordinary word" into an "ordinary_word".
     *
     * @param  string $word  Word to underscore
     * @return string Underscored word
     */
    public static function underscore($word);

    /**
     * Convert any "CamelCased" word into an array of strings
     *
     * Returns an array of strings each of which is a substring of string formed by splitting it at the camelcased `
     * letters.
     *
     * @param   string  $word Word to explode
     * @return  array   Array of strings
     */
    public static function explode($word);

    /**
     * Convert  an array of strings into a "CamelCased" word
     *
     * @param  array    $words   Array to implode
     * @return string  UpperCamelCasedWord
     */
    public static function implode($words);

    /**
     * Check to see if an English word is singular
     *
     * @param string $string The word to check
     * @return boolean
     */
    public static function isSingular($string);

    /**
     * Check to see if an English word is plural
     *
     * @param string $string
     * @return boolean
     */
    public static function isPlural($string);

    /**
     * Gets a part of a CamelCased word by index
     *
     * Use a negative index to start at the last part of the word (-1 is the last part)
     *
     * @param   string  $string  Word
     * @param   integer $index   Index of the part
     * @param   string  $default Default value
     *
     * @return  string
     */
    public static function getPart($string, $index, $default = null);
}
