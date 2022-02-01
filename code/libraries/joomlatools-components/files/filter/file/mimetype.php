<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Mimetype Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterFileMimetype extends KFilterAbstract
{
    public function validate($entity)
	{
	    if ($container = $entity->getContainer())
        {
            $mimetypes = KObjectConfig::unbox($container->getParameters()->allowed_mimetypes);

            if (is_array($mimetypes))
            {
                $mimetype = $entity->mimetype;

                if (empty($mimetype))
                {
                    if (is_uploaded_file(str_replace(chr(0), '', $entity->file))) {
                        $mimetype = $this->getObject('com:files.mixin.mimetype')->getMimetype($entity->file);
                    }
                    elseif ($entity->file instanceof SplFileInfo) {
                        $mimetype = $this->getObject('com:files.mixin.mimetype')->getMimetype($entity->file->getPathname());
                    }
                }

                if ($mimetype && !in_array($mimetype, $mimetypes)) {
                    return $this->_error($this->getObject('translator')->translate('Invalid Mimetype'));
                }
            }
        }
	}
}
