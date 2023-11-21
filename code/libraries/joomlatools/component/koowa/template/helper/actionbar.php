<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Action bar Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperActionbar extends KTemplateHelperActionbar
{
    /**
     * Render the action bar commands
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function render($config = array())
    {
        // Load the language strings for toolbar button labels
        JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

        $html = parent::render($config);

        return $html;
    }

    /**
     * Use Bootstrap 2.3.2 classes for buttons in frontend
     * @param array $config
     * @return string
     */
    public function command($config = array())
    {
        if (JFactory::getApplication()->isClient('site'))
        {
            $config = new KObjectConfigJson($config);
            $config->append(array(
                'command' => NULL
            ));

            $command = $config->command;

            if ($this->getTemplate()->decorator() == 'joomla')
            {
                $command->attribs->class->append(array('btn'));

                if ($command->id === 'new' || $command->id === 'apply') {
                    $command->attribs->class->append(array('btn-success'));
                }
            }
        }

        return parent::command($config);
    }

    /**
     * Render an options button
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function options($config = array())
    {
        return $this->dialog($config);
    }
}
