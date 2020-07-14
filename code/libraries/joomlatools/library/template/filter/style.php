<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Style Template Filter
 *
 * Filter to parse style tags
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Filter
 */
class KTemplateFilterStyle extends KTemplateFilterTag
{
    /**
     * Parse the text for style tags
     *
     * @param string $text  The text to parse
     * @return  string
     */
    protected function _parseTags(&$text)
    {
        $tags = '';

        $matches = array();
        if(preg_match_all('#<ktml:style\s+src="([^"]+)"(.*)\/>#siU', $text, $matches))
        {
            foreach(array_unique($matches[1]) as $key => $match)
            {
                //Set required attributes
                $attribs = array(
                    'src' => $match
                );

                $attribs = array_merge($this->parseAttributes( $matches[2][$key]), $attribs);
                $tags .= $this->_renderTag($attribs);
            }

            $text = str_replace($matches[0], '', $text);
        }

        $matches = array();
        if(preg_match_all('#<style(.*)>(.*)<\/style>#siU', $text, $matches))
        {
            foreach($matches[2] as $key => $match)
            {
                $attribs = $this->parseAttributes( $matches[1][$key]);
                $tags .= $this->_renderTag($attribs, $match);
            }

            $text = str_replace($matches[0], '', $text);
        }

        return $tags;
    }

    /**
     * Render the tag
     *
     * @param   array   $attribs Associative array of attributes
     * @param   string  $content The tag content
     * @return string
     */
    protected function _renderTag($attribs = array(), $content = null)
    {
        $link      = isset($attribs['src']) ? $attribs['src'] : false;
        $condition = isset($attribs['condition']) ? $attribs['condition'] : false;

        unset($attribs['condition']);

        if(!$link)
        {
            $attribs = $this->buildAttributes($attribs);

            $style  = '<style'.$attribs.'>'."\n";
            $style .= trim($content);
            $style .= '</style>'."\n";
        }
        else
        {
            unset($attribs['src']);
            $attribs = $this->buildAttributes($attribs);

            $style = '<link rel="stylesheet" href="'.$link.'" '.$attribs.' />'."\n";
        }

        if($condition)
        {
            $html  = '<!--[if '.$condition.']>'."\n";
            $html .=  $style;
            $html .= '<![endif]-->'."\n";
        }
        else $html = $style;

        return $html;
    }
}