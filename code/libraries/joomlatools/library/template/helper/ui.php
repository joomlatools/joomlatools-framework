<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Behavior Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class KTemplateHelperUi extends KTemplateHelperAbstract
{
    /**
     * Loads the common UI libraries
     *
     * @param array $config
     * @return string
     */
    public function load($config = array())
    {
        $identifier = $this->getTemplate()->getIdentifier();

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => false,
            'package' => $identifier->package,
            'domain'  => $identifier->domain,
            'type'    => $identifier->type,
            'styles' => array(),
        ))->append(array(
            'k_ui_container' => ($config->domain === 'admin' || $config->domain === '') && $config->type === 'com'
        ))->append(array(
            'wrapper_class' => array(
                // Only add k-ui-container for top-level component templates
                ($config->k_ui_container ? 'k-ui-container'.($config->debug ? '' : ' k-no-css-errors') : ''),
                'k-ui-namespace',
                $identifier->type.'_'.$identifier->package
            ),
        ))->append(array(
            'wrapper' => sprintf('<div class="%s">
                <!--[if lte IE 8 ]><div class="old-ie"><![endif]-->
                %%s
                <!--[if lte IE 8 ]></div><![endif]-->
                </div>', implode(' ', KObjectConfig::unbox($config->wrapper_class))
            )
        ));


        $html = '';

        if ($config->styles !== false)
        {
            if ($config->package) {
                $config->styles->package = $config->package;
            }

            if ($config->domain) {
                $config->styles->domain = $config->domain;
            }

            $config->styles->debug = $config->debug;

            $html .= $this->styles($config->styles);
        }

        $html .= $this->scripts($config);

        if ($config->wrapper) {
            $html .= $this->wrapper($config);
        }

        return $html;
    }

    public function styles($config = array())
    {
        $identifier = $this->getTemplate()->getIdentifier();

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => false,
            'package' => $identifier->package,
            'domain'  => $identifier->domain
        ))->append(array(
            'folder' => 'com_'.$config->package,
            'file'   => ($identifier->type === 'mod' ? 'module' : $config->domain) ?: 'admin'
        ));

        $html = '';

        if (empty($config->css_file) && $config->css_file !== false)
        {
            if (!$config->debug) {
                $config->file .= '.min';
            }

            $config->css_file = sprintf('%scss/%s.css', (empty($config->folder) ? '' : $config->folder.'/'), $config->file);
        }

        if ($config->css_file) {
            $html .= '<ktml:style src="assets://'.$config->css_file.'" />';
        }

        return $html;
    }

    public function scripts($config = array())
    {
        $identifier = $this->getTemplate()->getIdentifier();

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => false,
            'domain'  => $identifier->domain
        ));

        $html = '';

        if (!KTemplateHelperBehavior::isLoaded('k-js-enabled'))
        {
            $html .= '<script data-inline type="text/javascript">document.documentElement.classList.add(\'k-js-enabled\');</script>';

            KTemplateHelperBehavior::setLoaded('k-js-enabled');
        }

        $html .= $this->getTemplate()->helper('behavior.modernizr', $config->toArray());

        if (($config->domain === 'admin' || $config->domain === '')  && !KTemplateHelperBehavior::isLoaded('admin.js')) {
            // Make sure jQuery is always loaded right before admin.js, helps when wrapping components
            KTemplateHelperBehavior::setLoaded('jquery', false);

            $html .= $this->getTemplate()->helper('behavior.jquery', $config->toArray());
            $html .= '<ktml:script src="assets://js/admin'.($config->debug ? '' : '.min').'.js" />';

            KTemplateHelperBehavior::setLoaded('admin.js');
            KTemplateHelperBehavior::setLoaded('modal');
            KTemplateHelperBehavior::setLoaded('select2');
            KTemplateHelperBehavior::setLoaded('tooltip');
            KTemplateHelperBehavior::setLoaded('tree');
            KTemplateHelperBehavior::setLoaded('calendar');
            KTemplateHelperBehavior::setLoaded('tooltip');
            KTemplateHelperBehavior::setLoaded('validator');
        }

        $html .= $this->getTemplate()->helper('behavior.koowa', $config->toArray());

        return $html;
    }


    public function wrapper($config = array())
    {
        $config = new KObjectConfigJson($config);

        $this->getTemplate()->addFilter('wrapper');
        $this->getTemplate()->getFilter('wrapper')->setWrapper($config->wrapper);

        return '<ktml:template:wrapper>'; // used to make sure the template only wraps once
    }
}