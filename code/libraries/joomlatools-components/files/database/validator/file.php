<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * File Validator Command
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesDatabaseValidatorFile extends ComFilesDatabaseValidatorNode
{
	protected function _beforeSave(KDatabaseContextInterface $context)
	{
		$entity = $context->subject;

		if (is_string($entity->file) && !is_uploaded_file(str_replace(chr(0), '', $entity->file)))
		{
			// remote file
            $file = $this->getObject('com:files.model.entity.url');
            $file->setProperties(array('file' => $entity->file));

            if (!$file->contents) {
                throw new RuntimeException('File cannot be downloaded');
            }

            $entity->contents = $file->contents;

			if (empty($entity->name))
			{
				$uri = $this->getObject('lib:http.url', array('url' => $entity->file));
	        	$path = $uri->toString(KHttpUrl::PATH);
	        	if (strpos($path, '/') !== false) {
	        		$path = \Koowa\basename($path);
	        	}

	        	$entity->name = $path;
			}
		}

        $result = parent::_beforeSave($context);

        if ($result)
        {
            $filter = $this->getObject('com:files.filter.file.uploadable');
            $result = $filter->validate($context->getSubject());
            if ($result === false)
            {
                $errors = $filter->getErrors();
                if (count($errors)) {
                    $context->getSubject()->setStatusMessage(array_shift($errors));
                }
            }
        }

		return $result;

	}
}
