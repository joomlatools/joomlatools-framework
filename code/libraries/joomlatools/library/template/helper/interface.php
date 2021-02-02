<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Template Helper Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Helper
 */
interface KTemplateHelperInterface
{
    /**
     * Get the template object
     *
     * @return  object	The template object
     */
    public function getTemplate();

    /**
     * Build an HTML element
     *
     * @param string $tag HTML tag name
     * @param array  $attributes Key/Value pairs for the attributes
     * @param string|array|callable $children Child elements, not applicable for self-closing tags
     * @return string
     *
     * Example:
     * ```php
     * echo $this->buildElement('a', ['href' => 'https://example.com/'], 'example link');
     * // returns '<a href="https://example.com/">example link</a>
     *
     * echo $this->buildElement('meta', ['name' => 'foo', 'content' => 'bar']);
     * // returns '<meta name="foo" content="bar" />
     *
     * ```
     */
    public function buildElement($tag, $attributes = [], $children = '');

    /**
     * Method to build a string with xml style attributes from  an array of key/value pairs
     *
     * @param   mixed   $array The array of Key/Value pairs for the attributes
     * @return  string  String containing xml style attributes
     */
    public function buildAttributes($array);
}
