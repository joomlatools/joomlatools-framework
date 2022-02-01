<?php
/**
 * @package     Joomlatools Migrator
 * @copyright   Copyright (C) 2016 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComMigratorDispatcherBehaviorMigratable extends KControllerBehaviorAbstract
{
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'exporters' => array(),
            'importers' => array(),
        ));

        parent::_initialize($config);
    }

    protected function _beforeDispatch(KDispatcherContextInterface $context)
    {
        $query = $context->request->query;

        if (in_array($query->view, array('export', 'import')))
        {
            $exporters = $this->getConfig()->exporters->toArray();
            $importers = $this->getConfig()->importers->toArray();

            if (!count($exporters)) {
                $package = $this->getMixer()->getIdentifier()->getPackage();
                $exporters[$package] = sprintf('com://admin/%s.migrator.export', $package);
            }

            if (!count($importers)) {
                $package = $this->getMixer()->getIdentifier()->getPackage();
                $importers[$package] = sprintf('com://admin/%s.migrator.import', $package);
            }

            $this->getIdentifier('com:migrator.controller.export')->getConfig()->append(array(
                'exporters' => $exporters
            ));

            $this->getIdentifier('com:migrator.controller.import')->getConfig()->append(array(
                'importers' => $importers
            ));

            $context->param = 'com:migrator.dispatcher.http';
            $this->getMixer()->execute('forward', $context);
            $this->send();
        }
    }
}
