<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComMigratorControllerPermissionImport extends ComKoowaControllerPermissionAbstract
{
    /**
     * Only people who are able to manage EXTman can see it
     *
     * @return bool
     */
    public function canRender()
    {
        return $this->canManage();
    }

    /**
     * Only people who are able to manage EXTman can run it
     *
     * @return bool
     */
    public function canRun()
    {
        return $this->canManage();
    }
}
