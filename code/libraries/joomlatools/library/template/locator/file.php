<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * File Template Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Locator
 */
class KTemplateLocatorFile extends KTemplateLocatorAbstract
{
    /**
     * The locator name
     *
     * @var string
     */
    protected static $_name = 'file';

    /**
     * Find a template path
     *
     * @param array  $info  The path information
     * @return string|false The real template path or FALSE if the template could not be found
     */
    public function find(array $info)
    {
        $file   = pathinfo($info['url'], PATHINFO_FILENAME);
        $format = pathinfo($info['url'], PATHINFO_EXTENSION);

        $path = dirname($info['url']);
        $path = str_replace(parse_url($path, PHP_URL_SCHEME).'://', '', $path);

        if(!$result = $this->realPath($path.'/'.$file.'.'.$format))
        {
            $pattern = $path.'/'.$file.'.'.$format.'.*';
            $results = glob($pattern);

            //Try to find the file
            if ($results)
            {
                foreach($results as $file)
                {
                    if($result = $this->realPath($file)) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Qualify a template url
     *
     * @param  string $url   The template to qualify
     * @param  string $base  A fully qualified template url used to qualify.
     * @return string|false The qualified template path or FALSE if the path could not be qualified
     */
    public function qualify($url, $base)
    {
        if(!parse_url($url, PHP_URL_SCHEME))
        {
            if ($url[0] != '/')
            {
                //Relative path
                $url = dirname($base) . '/' . $url;
            }
            else
            {
                //Absolute path
                $url = parse_url($base, PHP_URL_SCHEME) . ':/' . $url;
            }
        }

        return $this->normalise($url);
    }

    /**
     * Normalise a template url
     *
     * Resolves references to /./, /../ and extra / characters in the input path and
     * returns the canonicalize absolute url. Equivalent of realpath() method.
     *
     * @param  string $url   The template to normalise
     * @return string|false  The normalised template url
     */
    public function normalise($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $path   = str_replace(array('/', '\\', $scheme.'://'), DIRECTORY_SEPARATOR, $url);

        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');

        $absolutes = array();
        foreach ($parts as $part)
        {
            if ('.' == $part) {
                continue;
            }

            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        $path = implode(DIRECTORY_SEPARATOR, $absolutes);
        $url  = $scheme ? $scheme.'://'.$path : $path;

        return $url;
    }
}
