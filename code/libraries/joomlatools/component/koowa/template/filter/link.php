<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Script Template Filter
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Filter
 */
class ComKoowaTemplateFilterLink extends KTemplateFilterLink
{
    /**
     * Find any virtual tags and render them
     *
     * This function will pre-pend the tags to the content
     *
     * @param string $text  The text to parse
     */
    public function filter(&$text)
    {
        $links   = $this->_parseTags($text);

        if($this->getTemplate()->decorator() == 'koowa') {
            $text = str_replace('<ktml:link>', $links, $text);
        } else  {
            $text = $links.$text;
        }
    }

    /**
     * Render the tag
     *
     * @param 	array	$attribs Associative array of attributes
     * @param 	string	$content The tag content
     * @return string
     */
    protected function _renderTag($attribs = array(), $content = null)
    {
        if($this->getTemplate()->decorator() == 'joomla')
        {
            $link      = isset($attribs['href']) ? $attribs['href'] : false;
            $relType  = 'rel';
            $relValue = $attribs['rel'];
            unset($attribs['rel']);
            unset($attribs['href']);

            $this->getObject('joomla')->document->addHeadLink($link, $relValue, $relType, $attribs);
        }
        else return parent::_renderTag($attribs, $content);
    }
}
