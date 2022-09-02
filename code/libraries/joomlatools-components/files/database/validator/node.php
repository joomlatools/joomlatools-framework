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
class ComFilesDatabaseValidatorNode extends KCommandHandlerAbstract
{
	protected function _beforeSave(KDatabaseContextInterface $context)
	{
        $entity = $context->getSubject();

        if (!$entity->isNew() && !$entity->overwrite)
        {
            $translator = $this->getObject('translator');
            $entity->setStatusMessage($translator->translate('Resource already exists'));
            return false;
        }

		return true;
	}

	protected function _beforeCopy(KDatabaseContextInterface $context)
	{
		$entity        = $context->subject;
		$translator = $this->getObject('translator');

		if (!array_intersect(array('destination_folder', 'destination_name'), array_keys($entity->getProperties(true))))
        {
            $entity->setStatusMessage($translator->translate('Please supply a destination.'));
			return false;
		}

		if ($entity->fullpath === $entity->destination_fullpath)
        {
            $entity->setStatusMessage($translator->translate('Source and destination are the same.'));
			return false;
		}

		$dest_adapter = $this->getObject(sprintf('com:files.adapter.%s', $entity->getIdentifier()->name), array(
			'path' => $entity->destination_fullpath
		));

		$exists = $dest_adapter->exists();

		if ($exists)
		{
			if (!$entity->overwrite)
            {
                $entity->setStatusMessage($translator->translate('Destination resource already exists.'));
				return false;
			}
            else $entity->overwritten = true;
		}

		return true;
	}

	protected function _beforeMove(KDatabaseContextInterface $context)
	{
		return $this->_beforeCopy($context);
	}
}
