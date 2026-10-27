<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Default Controller
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
abstract class ComFilesControllerAbstract extends ComKoowaControllerModel
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'formats'   => array('json')
        ));

        parent::_initialize($config);
    }

	public function getRequest()
	{
		$request = parent::getRequest();

		// "e_name" is needed to be compatible with com_content of Joomla
		if ($request->query->e_name) {
			$request->query->editor = $request->query->e_name;
		}
		
		return $request;
	}

	/**
	 * Refuses an action that has no node name to act on
	 *
	 * The models only filter on name when it is set, so without one the action would apply to every node in the
	 * folder. Non-local URIs are let through as they point to a single node without setting a name.
	 *
	 * @param KControllerContextInterface $context
	 * @throws KControllerExceptionRequestInvalid
	 */
	protected function _requireName(KControllerContextInterface $context)
	{
		$state = $this->getModel()->getState();

		if (!$state->name)
		{
			$scheme = $state->uri ? parse_url($state->uri, PHP_URL_SCHEME) : null;

			if (!$scheme || $scheme === 'file') {
				throw new KControllerExceptionRequestInvalid('Name is required');
			}
		}
	}

	protected function _actionCopy(KControllerContextInterface $context)
	{
		$entities = $this->getModel()->fetch();

		if(!$entities->isNew())
		{
            foreach($entities as $entity) {
                $entity->setProperties($context->request->data->toArray());
            }

			//Only throw an error if the action explicitly failed.
			if($entities->copy() === false)
			{
				$error = $entities->getStatusMessage();
				throw new KControllerExceptionActionFailed($error ? $error : 'Copy Action Failed');
			}
			else $context->status = $entities->getStatus() === KDatabase::STATUS_CREATED ? KHttpResponse::CREATED : KHttpResponse::NO_CONTENT;
		}
		else throw new KControllerExceptionResourceNotFound('Resource Not Found');

		return $entities;
	}

	protected function _actionMove(KControllerContextInterface $context)
	{
		$entities = $this->getModel()->fetch();

		if(!$entities->isNew())
		{
            foreach($entities as $entity) {
                $entity->setProperties($context->request->data->toArray());
            }

			//Only throw an error if the action explicitly failed.
			if($entities->move() === false)
			{
				$error = $entities->getStatusMessage();
				throw new KControllerExceptionActionFailed($error ? $error : 'Move Action Failed');
			}
			else $context->status = $entities->getStatus() === KDatabase::STATUS_CREATED ? KHttpResponse::CREATED : KHttpResponse::NO_CONTENT;
		}
		else throw new KControllerExceptionResourceNotFound('Resource Not Found');

		return $entities;
	}
}
