<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Mimetype Mixin
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesMixinMimetype extends KObject
{
	/**
	 * Used as a way to continue on the chain when the method is not available.
	 */
	const NOT_AVAILABLE = -1;

	/**
	 * Adapters to use for mimetype detection
	 *
	 * @var array
	 */
	protected $_adapters = array();

	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

		if (isset($config->adapters)) {
			$this->_adapters = KObjectConfig::unbox($config->adapters);
		}
	}

	protected function _initialize(KObjectConfig $config)
	{
		if (empty($config->adapters)) {
			$config->adapters = array('image', 'extension');
		}

		parent::_initialize($config);
	}

	public function getMimetype($path)
	{
		$mimetype = false;

		if (!file_exists($path)) {
			return $mimetype;
		}

		foreach ($this->_adapters as $i => $adapter)
		{
			$function = '_detect'.ucfirst($adapter);
			$return = $this->$function($path);

			if (!empty($return) && $return !== ComFilesMixinMimetype::NOT_AVAILABLE) {
				$mimetype = $return;
				break;
			}
		}

		// strip charset from text files
		if (!empty($mimetype) && strpos($mimetype, ';')) {
			$mimetype = substr($mimetype, 0, strpos($mimetype, ';'));
		}

		// special case: empty text files
		if ($mimetype == 'application/x-empty' || $mimetype === 'inode/x-empty') {
			$mimetype = 'text/plain';
		}

        // special case: Microsoft BMP mimetype
        if ($mimetype == 'image/x-ms-bmp') {
            $mimetype = 'image/bmp';
        }
		
		return $mimetype;
	}

	protected function _detectImage($path)
	{
		if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ComFilesModelEntityFile::$extension_type_map['image'])
			&& ($info = @getimagesize($path))
        ) {
			return $info['mime'];
		}

		return ComFilesMixinMimetype::NOT_AVAILABLE;
	}

	protected function _detectExtension($path)
    {
        $mimetype = ComFilesMixinMimetype::NOT_AVAILABLE;

        if ($extension = pathinfo($path, PATHINFO_EXTENSION))
        {
            $entity = $this->getObject('com:files.model.mimetypes')->extension($extension)->fetch();

            if (!$entity->isNew()) {
                $mimetype = $entity->mimetype;
            }
        }

        return $mimetype;
    }

	protected function _detectFinfo($path)
	{
		if (!class_exists('finfo')) {
			return ComFilesMixinMimetype::NOT_AVAILABLE;
		}

		$finfo = @new finfo(FILEINFO_MIME);
		
		if (empty($finfo)) {
		    return ComFilesMixinMimetype::NOT_AVAILABLE;
		}
		
		$mimetype = $finfo->file($path);

		return $mimetype;
	}

	/**
	 * Not used by default since it can't use our magic.mime file and cannot be reliable.
	 * It's also deprecated by PHP in favor of fileinfo extension used above.
	 */
	protected function _detectMime_content_type($path)
	{
		if (!function_exists('mime_content_type')) {
			return ComFilesMixinMimetype::NOT_AVAILABLE;
		}

		return mime_content_type($path);
	}
}
