<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachable Model behavior
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesModelBehaviorAttachable extends ComFilesModelBehaviorRelatable
{
    /**
     * Overridden to include creatable info.
     */
    protected function _beforeFetch(KModelContextInterface $context)
    {
        parent::_beforeFetch($context);

        if ($context->getName() != 'before.count')
        {
            $state = $context->getState();
            $query = $context->query;

            if ($state->table || $state->row)
            {
                $query->join('users AS users', 'relations.created_by = users.id', 'LEFT');

                $query->columns(array(
                    'attached_by'      => 'relations.created_by',
                    'attached_on'      => 'relations.created_on',
                    'attached_by_name' => 'users.name'
                ));
            }
        }
    }
}