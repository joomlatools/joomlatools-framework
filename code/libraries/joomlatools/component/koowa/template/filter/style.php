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
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Filter
 */
class ComKoowaTemplateFilterStyle extends KTemplateFilterStyle
{
    /**
     * An array of MD5 hashes for loaded style strings
     */
    protected $_loaded = array();

    /**
     * Find any virtual tags and render them
     *
     * This function will pre-pend the tags to the content
     *
     * @param string $text  The text to parse
     */
    public function filter(&$text)
    {
        $styles = $this->_parseTags($text);

        if($this->getTemplate()->decorator() == 'koowa') {
            $text = str_replace('<ktml:style>', $styles, $text);
        } else  {
            $text = $styles.$text;
        }
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
        if($this->getTemplate()->decorator() == 'joomla')
        {
            $link      = isset($attribs['src']) ? $attribs['src'] : false;
            $condition = isset($attribs['condition']) ? $attribs['condition'] : false;

            if(!$link)
            {
                $hash  = md5($content.serialize($attribs));

                if (!isset($this->_loaded[$hash]))
                {
                    if($condition)
                    {
                        $script = parent::_renderTag($attribs, $content);
                        JFactory::getDocument()->addCustomTag($script);
                    }
                    else JFactory::getDocument()->addStyleDeclaration($content);

                    $this->_loaded[$hash] = true;
                }
            }
            else
            {
                if($condition)
                {
                    $style = parent::_renderTag($attribs, $content);
                    JFactory::getDocument()->addCustomTag($style);
                }
                else
                {
                    unset($attribs['src']);
                    unset($attribs['condition']);

                    JFactory::getDocument()->addStyleSheet($link, [], $attribs);
                }
            }
        }
        else return parent::_renderTag($attribs, $content);
    }
}
