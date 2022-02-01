<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Name Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterFileName extends KFilterAbstract
{
    protected static $_rejected_names = array('.htaccess', 'web.config', 'index.htm', 'index.html', 'index.php', '.svn', '.git', 'cvs');

    public function validate($entity)
	{
        $value = $this->sanitize($entity->name);

        if (in_array(strtolower($value), self::$_rejected_names))
        {
            throw new KControllerExceptionActionFailed($this->getObject('translator')->translate(
                'You cannot upload a file named {filename} for security reasons.',
                array('filename' => $value)
            ));
        }

		if ($value == '') {
            return $this->_error($this->getObject('translator')->translate('Invalid file name'));
		}

        return true;
	}

	public function sanitize($value)
	{
		return $this->getObject('com:files.filter.path')->sanitize($value);
	}
}
