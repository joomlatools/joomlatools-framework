<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Modal Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperUploader extends KTemplateHelperAbstract
{
    /**
     * Array which holds a list of loaded Javascript libraries
     *
     * @type array
     */
    protected static $_loaded = array();

    public function container($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'container' => null,
            'options'   => array(
                'multipart_params' => array(
                    '_actionAdd' => 'add',
                    'folder'     => ''
                ),
                'check_duplicates' => true,
                'chunking' => true
            )
        ));

        // set container
        if ($config->container) {
            $container = $this->getObject('com:files.model.containers')->slug($config->container)->fetch()->top();

            if ($container) {
                $container = $container->toArray();
            }

            $config->options->container = $container;
        }

        $html = $this->uploader($config);

        return $html;
    }

    public function scripts($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append([
            'debug' => JFactory::getApplication()->getCfg('debug')
        ]);

        $html = '';

        if(!isset(static::$_loaded['uploader']))
        {
            $template = $this->getTemplate();
            $filter   = null;
            $wrapper  = null;

            if ($template->hasFilter('wrapper'))
            {
                $filter = $template->getFilter('wrapper');
                $wrapper = $filter->getWrapper();
                $filter->setWrapper(null);
            }

            $html .= $template->loadFile('com:files.files.uploader_scripts.html')->render($config->toArray());

            if ($template->hasFilter('wrapper')) {
                $filter->setWrapper($wrapper);
            }

            static::$_loaded['uploader'] = true;
        }

        return $html;
    }

    public function uploader($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'element'   => null,
            'attributes' => array(
                'class' => array('k-upload')
            ),
            'options'   => array(
                'url'       => null,
                'multipart_params' => array(
                    'csrf_token' => $this->getObject('user')->getSession()->getToken()
                ),
                'multi_selection' => false,
                'autostart' =>  true
            )
        ))->append(array(
            'selector' => $config->element
        ));

        $html = $this->scripts($config);

        if (is_object($config->options->url)) {
            $config->options->url = (string) $config->options->url;
        }

        $html .= '<script>
            kQuery(function($){
                $("'.$config->selector.'").uploader('.$config->options.');
            });</script>';

        if ($config->element) {
            $element    = $config->element;
            $attributes = $config->attributes->toArray();

            if ($element[0] === '#') {
                $attributes['id'] = substr($element, 1);
            } else {
                $attributes['class'][] = substr($element, 1);
            }

            $html .= sprintf('<div %s></div>', $this->buildAttributes($attributes));
        }

        return $html;
    }
}