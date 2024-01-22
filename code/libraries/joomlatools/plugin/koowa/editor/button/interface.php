<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Koowa editor button plugin interface
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Plugin\Koowa
 */
interface PlgKoowaEditorButtonInterface
{
    /**
     * Editor setter
     *
     * @param $string The editor name
     */
    public function setEditor($name);

    /**
     * Editor getter
     *
     * @return string The editor name
     */
    public function getEditor();
}