<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Size Filter
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesFilterFileSize extends KFilterAbstract
{
    public function validate($entity)
	{
	    if ($container = $entity->getContainer())
        {
            $max = $container->getParameters()->maximum_size;

            if ($max)
            {
                $size = $entity->contents ? strlen($entity->contents) : false;
                if (!$size && is_uploaded_file(str_replace(chr(0), '', $entity->file))) {
                    $size = filesize($entity->file);
                } elseif ($entity->file instanceof SplFileInfo && $entity->file->isFile()) {
                    $size = $entity->file->getSize();
                }

                if ($size && $size > $max) {
                    return $this->_error($this->getObject('translator')->translate('File is too big'));
                }
            }
        }
	}
}
