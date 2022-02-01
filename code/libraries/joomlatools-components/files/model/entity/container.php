<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Container Entity
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesModelEntityContainer extends KModelEntityRow
{
    public function getPropertyRelativePath()
    {
        $path = $this->fullpath;
        $root = str_replace('\\', '/', Koowa::getInstance()->getRootPath());

        return str_replace($root.'/', '', $path);
    }

    public function getPropertyFullpath()
    {
        $result = $this->getProperty('path');

        // Prepend with site root if it is a relative path
        if (!preg_match('#^(?:[a-z]\:|~*/)#i', $result)) {
            $result = Koowa::getInstance()->getRootPath().'/'.$result;
        }

        $result = rtrim(str_replace('\\', '/', $result), '\\');

        return $result;
    }

	public function toArray()
	{
		$data = parent::toArray();
        $data['relative_path'] = $this->getProperty('relative_path');
		$data['parameters']    = $this->getParameters()->toArray();
        $data['server_upload_limit'] = static::getServerUploadLimit();

		return $data;
	}

    /**
     * Finds the maximum possible upload size based on a few different INI settings
     *
     * @return int
     */
    public static function getServerUploadLimit()
    {
        $convertToBytes = function($value) {
            $keys = array('k', 'm', 'g');
            $last_char = strtolower(substr($value, -1));
            $value = (int) $value;

            if (in_array($last_char, $keys)) {
                $value *= pow(1024, array_search($last_char, $keys)+1);
            }

            return $value;
        };

        $max_upload = $convertToBytes(ini_get('upload_max_filesize'));
        $max_post   = $convertToBytes(ini_get('post_max_size'));

        return min($max_post, $max_upload);
    }
}
