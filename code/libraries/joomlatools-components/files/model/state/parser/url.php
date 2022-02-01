<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Model State Parser Url
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelStateParserUrl extends KObject implements KObjectMultiton
{
    public function parse($value)
    {
        $value = rawurldecode($value);

        $result = new stdClass();

        $parts = explode('://', $value);

        $result->container = null;

        if (count($parts) > 1)
        {
            $result->scheme = $parts[0];

            if ($result->scheme == 'file' && strpos($parts[1], '/') !== 0) {
                $result->container = substr($parts[1],0, strpos($parts[1], '/'));
            }

            $path = $parts[1];

            if ($container = $result->container) {
                $path = str_replace($container, '', $path);
            }

            $result->path = $path;
        }
        else
        {
            $result->scheme = null;
            $result->path   = $value;
        }

        return $result;
    }
}