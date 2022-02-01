<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Container Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterContainer extends KFilterAbstract
{
    public function validate($data)
    {
        if (is_string($data)) {
            return $this->getObject('lib:filter.cmd')->validate($data);
        } else if (is_object($data)) {
            return true;
        }

        return false;
    }

    public function sanitize($data)
    {
        if (is_string($data)) {
            return $this->getObject('lib:filter.cmd')->sanitize($data);
        } else if (is_object($data)) {
            return $data;
        }

        return null;
    }
}
