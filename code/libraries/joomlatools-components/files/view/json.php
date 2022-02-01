<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Json View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesViewJson extends KViewJson
{
    protected function _renderData()
    {
        $output = parent::_renderData();

        if (!$this->isCollection())
        {
            $entity    = $this->getModel()->fetch();
            $status = $entity->getStatus() !== KDatabase::STATUS_FAILED;

            $output['status'] = $status;

            if ($status === false){
                $output['error'] = $entity->getStatusMessage();
            }
        }

        return $output;
    }
}
