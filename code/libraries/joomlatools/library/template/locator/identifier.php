<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Identifier Template Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Locator
 */
abstract class KTemplateLocatorIdentifier extends KTemplateLocatorAbstract
{
    /**
     * Locate the template
     *
     * @param  string $url   The template url
     * @return string|false The real template path or FALSE if the template could not be found
     */
    public function locate($url)
    {
        if(!isset($this->_locations[$url]))
        {
            $layout = $this->getLayout($url);

            $info = array(
                'url'      => $url,
                'domain'   => $layout->getDomain(),
                'package'  => $layout->getPackage(),
                'path'     => $layout->getPath(),
            );

            $info += $this->parseIdentifier($url);

            $this->_locations[$url] = $this->find($info);
        }

        return $this->_locations[$url];
    }

    /**
     * Find the template path
     *
     * @param  string $url   The template to qualify
     * @param  string $base  A fully qualified template url used to qualify.
     * @return string|false The qualified template path or FALSE if the path could not be qualified
     */
    public function qualify($url, $base)
    {
        $url  = $this->parseIdentifier($url);
        $base = $this->parseIdentifier($base);

        $result = $base['package'].'.'.$base['path'].'.'.$url['file'].'.'.$url['format'];

        if(!empty($url['type'])) {
            $result .= '.'.$url['type'];
        }

        return $result;
    }

    /**
     * Get the layout identifier based on the url
     *
     * If the identifier has been aliased the alias will be returned.
     *
     * @param string $url  The template url
     * @return KObjectIdentifier
     */
    public function getLayout($url)
    {
        $engines = $this->getObject('template.engine.factory')->getFileTypes();
        $parts   = explode('.', $url);

        if(in_array(end($parts), $engines))
        {
            $type  =  array_pop($parts);
            $format = array_pop($parts);
        }
        else $format = array_pop($parts);

        return $this->getIdentifier(implode('.', $parts));
    }

    /**
     * Parse a template identifier
     *
     * @return array
     */
    public function parseIdentifier($url)
    {
        $engines = $this->getObject('template.engine.factory')->getFileTypes();

        //Set defaults
        $path   = null;
        $file   = null;
        $format = null;
        $type   = null;

        //Qualify partial templates.
        if(strpos($url, ':') === false)
        {
            /**
             * Parse identifiers in following formats :
             *
             * - '[file.[format].[type]';
             * - '[file].[format];
             */

            $parts = explode('.', $url);

            if(in_array(end($parts), $engines)) {
                $type = array_pop($parts);
            }

            $format = array_pop($parts);
            $file   = array_pop($parts);

            $info = array(
                'package' => '',
                'path'    => '',
                'file'   => $file,
                'format' => $format,
                'type'   => $type,
            );
        }
        else
        {
            /**
             * Parse identifiers in following formats :
             *
             * - '[package].[path].[file].[format].[type]';
             * - '[package].[path].[file].[format];
             */

            $parts = explode('.', $url);

            if(in_array(end($parts), $engines))
            {
                $type  =  array_pop($parts);
                $format = array_pop($parts);
                $file   = array_pop($parts);
            }
            else
            {
                $format = array_pop($parts);
                $file   = array_pop($parts);
            }

            $info = array(
                'package' => array_shift($parts),
                'path'    => implode('.', $parts),
                'file'   => $file,
                'format' => $format,
                'type'   => $type,
            );
        }

        return $info;
    }
}
