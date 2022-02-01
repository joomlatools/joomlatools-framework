<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-activities for the canonical source repository
 */

/**
 * Purgeable Controller Behavior.
 *
 * Adds purge action to the controller.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Activities
 */
class ComActivitiesControllerBehaviorPurgeable extends KControllerBehaviorAbstract
{
    /**
     * Purge action.
     *
     * Deletes activities given a date range.
     *
     * @param KControllerContextInterface $context A command context object.
     * @throws KControllerExceptionActionFailed If the activities cannot be purged.
     * @return KModelEntityInterface
     */
    protected function _actionPurge(KControllerContextInterface $context)
    {
        $model = $this->getModel();
        $state = $model->getState();
        $query = $this->getObject('lib:database.query.delete');

        $query->table(array($model->getTable()->getName()));

        if ($state->end_date && $state->end_date != '0000-00-00')
        {
            $end_date = $this->getObject('lib:date', array('date' => $state->end_date));
            $end      = $end_date->format('Y-m-d');

            $query->where('DATE(created_on) <= :end')->bind(array('end' => $end));
        }

        if (!$this->getModel()->getTable()->getAdapter()->execute($query)) {
            throw new KControllerExceptionActionFailed('Delete Action Failed');
        } else {
            $context->getResponse()->setStatus(KHttpResponse::NO_CONTENT);
        }
    }
}