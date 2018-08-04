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
     * The root path
     *
     * @var string
     */
    protected $_base_path;

    /**
     * Constructor
     *
     * Prevent creating instances of this class by making the constructor private
     *
     * @param KObjectConfig $config   An optional ObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_base_path = rtrim($config->base_path, '/');
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config  An optional ObjectConfig object with configuration options.
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'base_path' =>  '',
        ));

        parent::_initialize($config);
    }

    /**
     * Get the root path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_base_path;
    }

    /**
     * Find a template path
     *
     * @param array  $info  The path information
     * @return string|false The real template path or FALSE if the template could not be found
     */
    public function find(array $info)
    {
        $path = str_replace(parse_url($info['url'], PHP_URL_SCHEME).'://', '', $info['url']);

        $file   = pathinfo($path, PATHINFO_FILENAME);
        $format = pathinfo($path, PATHINFO_EXTENSION);
        $path   = ltrim(pathinfo($path, PATHINFO_DIRNAME), '.');

        $parts = array();

        //Add the base path
        if($base = $this->getBasePath()) {
            $parts[] = $base;
        }

        //Add the file path
        if($path) {
            $parts[] = $path;
        }

        //Add the file
        $parts[] = $file;

        //Create the path
        $path = implode('/', $parts);

        //Append the format
        if($format) {
            $path = $path.'.'.$format;
        }

        if(!$result = $this->realPath($path))
        {
            $pattern = $path.'.*';
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

    /**
     * Prevent directory traversal attempts outside of the base path
     *
     * @param  string $file The file path
     * @return string The real file path
     */
    public function realPath($file)
    {
        $path = parent::realPath($file);

        if($base = $this->getBasePath())
        {
            if(strpos($file, $base) !== 0) {
                return false;
            }
        }

        return $path;
    }
}
