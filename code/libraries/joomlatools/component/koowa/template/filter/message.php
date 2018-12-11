<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Messages Template Filter
 *
 * Filter will render the response flash messages.
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Filter
 */
class ComKoowaTemplateFilterMessage extends KTemplateFilterAbstract
{
    public function filter(&$text)
    {
        if (strpos($text, '<ktml:messages>') !== false)
        {
            $output   = '';
            $messages = $this->getObject('response')->getMessages();

            foreach ($messages as $type => $message)
            {
                if ($type === 'notice') {
                    $type = 'info';
                } elseif ($type === 'error') {
                    $type = 'danger';
                }

                $output .= '<div class="k-alert k-alert--'.strtolower($type).'">';
                $output .= implode('<br>', $message);
                $output .= '</div>';
            }

            $text = str_replace('<ktml:messages>', $output, $text);
        }
    }
}