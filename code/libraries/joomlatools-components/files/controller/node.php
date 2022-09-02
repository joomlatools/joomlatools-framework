<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Node Controller
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesControllerNode extends ComFilesControllerAbstract
{
	protected function _initialize(KObjectConfig $config)
	{
		$config->append(array(
			'behaviors' => array('thumbnailable')
		));

		parent::_initialize($config);
	}

    protected function _beforeMove(KControllerContextInterface $context)
    {
        $request = $this->getRequest();

        if ($request->data->has('name')) {
            $request->query->name = $request->data->name;
            unset($request->data->name);
        }
    }

    protected function _beforeCopy(KControllerContextInterface $context)
    {
        $this->_beforeMove($context);
    }
}
