<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

namespace Koowa {
    /**
     * Returns trailing name component of path
     *
     * Fixes a PHP issue on some locales where if the first character of the filename is non-ASCII, it is stripped.
     * See: https://stackoverflow.com/questions/32115609/basename-fail-when-file-name-start-by-an-accent
     *
     * @param  string  $path A path. On Windows, both slash (/) and backslash (\) are used as directory separator character.
     * In other environments, it is the forward slash (/).
     * @param  string  $suffix If the name component ends in suffix this will also be cut off.
     * @return string  Returns the base name of the given path.
     */
    function basename($path, $suffix = null)
    {
        return substr(\basename(' '.strtr($path, array('/' => '/ ')), $suffix), 1);
    }
}

