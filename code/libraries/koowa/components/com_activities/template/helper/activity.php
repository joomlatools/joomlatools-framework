<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright      Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link           http://github.com/joomlatools/koowa-activities for the canonical source repository
 */

/**
 * Activity Template Helper
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesTemplateHelperActivity extends KTemplateHelperAbstract implements KObjectMultiton
{
    /**
     * Renders an activity
     *
     * @param  array $config An optional configuration array.
     * @throws InvalidArgumentException
     * @return  string  Html
     */
    public function render($config = array())
    {
        $config = new KObjectConfig($config);
        $config->append(array(
            'entity' => null,
        ));

        $activity = $config->entity;

        if (!$activity instanceof ComActivitiesActivityInterface) {
            throw new InvalidArgumentException('Activity Not Found');
        }

        //Render activity parameters
        foreach ($activity->getParameters() as $parameter)
        {
            $format = '<span class="text">%s</span>';

            if ($parameter->isLinkable())
            {
                $link = $parameter->getLink();

                $view       = $this->getTemplate()->getView();
                $url        = $view->getActivityRoute($link->href);
                $attributes = !empty($link->attribs) ? $this->buildAttributes($link->attribs) : '';

                $format = '<a ' . $this->_escapeString($attributes) . ' href="' . $this->_escapeString($url) . '">' . $format . '</a>';
            }

            $attribs = $parameter->getAttributes();

            if (count($attribs)) {
                $format = '<span ' . $this->_escapeString($this->buildAttributes($attribs)) . '>' . $format . '</span>';
            }

            $parameter->setFormat($format);
        }

        //Render activity message
        $html = '';
        $html .= $activity->toString();

        return $html;
    }

    /**
     * Escapes special sprintf characters.
     *
     * @param string $string The string to escape.
     *
     * @return mixed The escaped string.
     */
    protected function _escapeString($string)
    {
        return str_replace('%', '%%', $string);
    }
}
