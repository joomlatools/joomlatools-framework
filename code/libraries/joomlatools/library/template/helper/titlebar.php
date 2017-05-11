<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Title bar Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Library\Template\Helper
 */
class KTemplateHelperTitlebar extends KTemplateHelperToolbar
{
    public function getToolbarType()
    {
        return 'actionbar';
    }
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
            'title'   => null,
        ))->append(array(
            'icon' => $config->toolbar->getName()
        ));

        //Set a custom title
        if($config->title || $config->icon)
        {
            if($config->toolbar->hasCommand('title'))
            {
                $command = $config->toolbar->getCommand('title');

                if ($config->title) {
                    $command->set('title', $config->title);
                }

                if ($config->icon) {
                    $command->set('icon', $config->icon);
                }
            }
            else $config->toolbar->addTitle($config->title, $config->icon);
        }

        $html     = '';
        $commands = $config->toolbar->getCommands();

        foreach ($commands as $command)
        {
            if ($command->getName() === 'title')
            {
                $config->command = $command;

                $html .= $this->title($config);
            }
        }

        return $html;
    }

    /**
     * Render the action bar title
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function title($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'command' => NULL,
        ));

        $title = $this->getObject('translator')->translate($config->command->title);
        $html  = '';

        if (!empty($title))
        {
            $mobile = ($config->mobile === '' || $config->mobile) ? 'k-title-bar--mobile' : '';

            $html .= '
            <div class="k-title-bar k-js-title-bar '.$mobile.'">
                <div class="k-title-bar__heading">' . $title . '</div>
            </div>';
        }

        return $html;
    }
}
