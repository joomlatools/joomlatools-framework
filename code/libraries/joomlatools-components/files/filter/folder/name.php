<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Folder Name Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterFolderName extends KFilterAbstract
{
    protected static $_rejected_names = array('.htaccess', 'web.config', 'index.htm', 'index.html', 'index.php', '.svn', '.git', 'cvs');

    public function validate($entity)
	{
        $value = $entity->name;

        if (strpos($value, '/') !== false) {
            return $this->_error($this->getObject('translator')->translate('Folder names cannot contain slashes'));
		}

        $value = $this->sanitize($value);

        if (in_array(strtolower($value), self::$_rejected_names))
        {
            return $this->_error($this->getObject('translator')->translate(
                'You cannot create a folder named {foldername} for security reasons.',
                array('foldername' => $value)
            ));
        }

		if ($value == '') {
            return $this->_error($this->getObject('translator')->translate('Invalid folder name'));
		}

        return true;
	}

	public function sanitize($value)
	{
		$value = str_replace('/', '', $value);
		return $this->getObject('com:files.filter.path')->sanitize($value);
	}
}
