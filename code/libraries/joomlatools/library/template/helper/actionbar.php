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
 * @package Koowa\Library\Template\Helper
 */
class KTemplateHelperActionbar extends KTemplateHelperToolbar
{
    /**
     * Render the action bar commands
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function render($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'toolbar' => null,
        ));

        //Render the buttons
        $html = '';

        $commands = $config->toolbar->getCommands();

        if (count($commands))
        {
            $html .= '<div class="k-toolbar k-js-toolbar">';

            foreach ($commands as $command)
            {
                $name = $command->getName();

                if ($name === 'title') {
                    continue;
                }

                if(method_exists($this, $name)) {
                    $html .= $this->$name(array('command' => $command));
                } else {
                    $html .= $this->command(array('command' => $command));
                }
            }

            $html .= '</div>';
        }



        return $html;
    }

    /**
     * Render a action bar command
     *
     * @param   array|KObjectConfig   $config An optional array with configuration options
     * @return  string  Html
     */
    public function command($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'command' => NULL
        ));

        $translator = $this->getObject('translator');
        $command    = $config->command;

        if ($command->allowed === false)
        {
            $command->attribs->title = $translator->translate('You are not allowed to perform this action');
            $command->attribs->class->append(array('k-is-disabled', 'k-is-unauthorized'));
        }

         //Add a toolbar class	
        $command->attribs->class->append(array('toolbar'));

        //Create the href
        $command->attribs->append(array('href' => '#'));
        if(!empty($command->href)) {
            $command->attribs['href'] = $this->getTemplate()->route($command->href);
        }

        //Create the id
        $command->attribs->id = 'toolbar-'.$command->id;

        $command->attribs->class->append(array('k-button', 'k-button--default', 'k-button-'.$command->id));

        if ($command->id === 'new' || $command->id === 'apply') {
            $command->attribs->class->append(array('k-button--success'));
        }

        $attribs = clone $command->attribs;
        $attribs->class = implode(" ", KObjectConfig::unbox($attribs->class));

        $html = '<a '.$this->buildAttributes($attribs).'>';

        $html .= '<span class="'.$command->icon.'" aria-hidden="true"></span> ';
        $html .= '<span class="k-button__text">';
        $html .= $translator->translate($command->label);
        $html .= '</span>';
        $html .= '</a>';

        return $html;
    }

    /**
     * Render a separator
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function separator($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'command' => NULL
        ));

        return '';
    }

    /**
     * Render a modal button
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function dialog($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'command' => NULL
        ));

        $html = $this->command($config);

        return $html;
    }
}
