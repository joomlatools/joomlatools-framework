<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Template Locator Interface
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Locator
 */
interface KTemplateLocatorInterface
{
    /**
     * Get the locator name
     *
     * @return string The stream name
     */
    public static function getName();

    /**
     * Find the template path
     *
     * @param  string $url   The Template url
     * @return string|false The real template path or FALSE if the template could not be found
     */
    public function locate($url);

    /**
     * Find a template path
     *
     * @param array  $info The path information
     * @return string|false The real template path or FALSE if the template could not be found
     */
    public function find(array $info);

    /**
     *  Qualify a template url
     *
     * @param  string $url   The template to qualify
     * @param  string $base  A fully qualified template url used to qualify.
     * @return string|false The qualified template url or FALSE if the path could not be qualified
     */
    public function qualify($url, $base);

    /**
     * Get a path from an file
     *
     * Function will check if the path is an alias and return the real file path
     *
     * @param  string $file The file path
     * @return string The real file path
     */
    public function realPath($file);

    /**
     * Returns true if the template is still fresh.
     *
     * @param  string $url   The Template url
     * @param int     $time  The last modification time of the cached template (timestamp)
     * @return bool TRUE if the template is still fresh, FALSE otherwise
     */
    public function isFresh($url, $time);
}