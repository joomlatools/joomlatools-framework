<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Files Html View
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesViewFilesHtml extends ComKoowaViewHtml
{
    /**
     * @var string The root path
     */
    protected $_root_path = '';

	protected function _initialize(KObjectConfig $config)
	{
		$config->auto_fetch = false;

        $config->append([
            'decorator'  => $config->layout === 'select' ? 'koowa' : 'joomla'
        ]);

		parent::_initialize($config);
	}

    /**
     * Root path setter
     *
     * @param string $path The root path
     *
     * @return $this
     */
	public function setRootPath($path)
    {
        $this->_root_path = (string) $path;
    }

    /**
     * Root path getter
     *
     * @return string The root path
     */
    public function getRootPath()
    {
        return $this->_root_path;
    }

    protected function _fetchData(KViewContext $context)
	{
        $state     = $this->getModel()->getState();
        $container = $this->getModel()->getContainer();
        $query     = $state->getValues();

        $config = new KObjectConfig($state->config);

        $config->append(array(
            'router'           => array(
                'defaults' => array(
                    'option' => 'com_' . substr($container->slug, 0, strpos($container->slug, '-')),
                    'routed' => '1'
                )
            ),
            'initial_response' => false
        ))->append($this->getConfig()->config);

        if ($root_path = $this->getRootPath())
        {
            $config->append(array('active' => $root_path, 'root_path' => $root_path));
            $query['folder'] = $root_path; // Set folder to point to the new root
        }

        if ($config->initial_response === true)
        {
            $count = 0;
            unset($query['config']);
            $query['thumbnails'] = $this->getModel()->getContainer()->getParameters()->thumbnails;

            if (strpos($this->getLayout(), 'compact') !== false)
            {
                $query['limit'] = 0;
                $count = ComFilesIteratorDirectory::countNodes(array('path' => $this->getModel()->getPath()));
            }

            if ($count < 100)
            {
                $controller = $this->getObject('com:files.controller.node');
                $controller->getRequest()->setQuery($query);

                $config->initial_response = $controller->format('json')->render();
            }
            else unset($config->initial_response);
        }

        $state->config = $config->toArray();

        $context->data->sitebase  = trim(JURI::root(), '/');
        $context->data->token     = $this->getObject('user')->getSession()->getToken();
        $context->data->container = $container;
        $context->data->debug     = KClassLoader::getInstance()->isDebug();

        $query = $this->getUrl()->getQuery(true);

        $context->data->thumbnails = isset($query['thumbnails']) ? $query['thumbnails'] : null;

		parent::_fetchData($context);

        $context->parameters = $state->getValues();
        $context->parameters->config = $config;
    }
}
