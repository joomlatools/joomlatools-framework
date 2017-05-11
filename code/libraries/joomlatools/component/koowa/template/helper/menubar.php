<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Menu bar Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperMenubar extends KTemplateHelperToolbar
{
    /**
     * Render the menu bar
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function render($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'toolbar' => null
        ));

        $html = '<ul class="k-navigation">';

        foreach ($config->toolbar->getCommands() as $command)
        {
            $command->append(array(
                'attribs' => array(
                    'href' => '#',
                    'class' => array()
                )
            ));

            if(!empty($command->href)) {
                $command->attribs['href'] = $this->getTemplate()->route($command->href);
            }

            $url = KHttpUrl::fromString($command->attribs->href);

            if (isset($url->query['view'])) {
                $command->attribs->class->append('k-navigation-'.$url->query['view']);
            }

            $attribs = clone $command->attribs;
            $attribs->class = implode(" ", KObjectConfig::unbox($attribs->class));

            $html .= '<li'.($command->active ? ' class="k-is-active"' : '').'>';
            $html .= '<a '.$this->buildAttributes($attribs).'>';
            $html .= $this->getObject('translator')->translate($command->label);
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
