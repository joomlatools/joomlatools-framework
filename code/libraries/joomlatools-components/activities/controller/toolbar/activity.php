<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Activity Controller Toolbar.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesControllerToolbarActivity extends KControllerToolbarActionbar
{
    protected function _afterBrowse(KControllerContextInterface $context)
    {
        if ($this->getController()->canPurge()) {
            $this->addPurge();
        }

        return parent::_afterBrowse($context);
    }

    protected function _commandPurge(KControllerToolbarCommandInterface $command)
    {
        $command->append(array(
            'attribs' => array(
                'data-action'     => 'purge',
                'data-novalidate' => 'novalidate',
                'data-prompt'     => $this->getObject('translator')
                                          ->translate('Deleted items will be lost forever. Would you like to continue?')
            )
        ));
    }
}