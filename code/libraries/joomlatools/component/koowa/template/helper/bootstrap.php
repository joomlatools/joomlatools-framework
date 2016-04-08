<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/nooku/nooku-framework for the canonical source repository
 */

/**
 * Bootstrap Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperBootstrap extends ComKoowaTemplateHelperBehavior
{
    /**
     * Load Bootstrap JavaScript files, from Joomla if possible
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function javascript($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => JFactory::getApplication()->getCfg('debug')
        ));
        $html   = '';

        if (!isset(self::$_loaded['bootstrap-javascript']))
        {
            if (!isset(self::$_loaded['jquery'])) {
                $html .= $this->jquery($config);
            }

            JHtml::_('bootstrap.framework');
            self::$_loaded['bootstrap-javascript'] = true;
        }

        return $html;
    }

    /**
     * Loads necessary Bootstrap files
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function load($config = array())
    {
        $identifier = $this->getTemplate()->getIdentifier();

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug'          => JFactory::getApplication()->getCfg('debug'),
            'javascript'     => false,
            'package'        => $identifier->package,
            'file'           => $identifier->type === 'mod' ? 'module' : $identifier->domain,
            'load_base'      => false,
            'class'          => array(
                'koowa',
                $identifier->type.'_'.$identifier->package,
                JFactory::getLanguage()->isRTL() ? 'koowa--rtl' : '',
            ),
        ))->append(array(
            'wrapper' => sprintf('<div class="%s">
                <!--[if lte IE 8 ]><div class="old-ie"><![endif]-->
                %%s
                <!--[if lte IE 8 ]></div><![endif]-->
                </div>', implode(' ', KObjectConfig::unbox($config->class))
            )
        ));

        $html = '';

        if ($config->javascript && !isset(self::$_loaded['bootstrap-javascript'])) {
            $html .= $this->javascript($config);
        }

        // Load the generic files
        // We assume that the template has either loaded Bootstrap or provided styles for it in 3.0+
        if (!isset(self::$_loaded['bootstrap-css']))
        {
            $template = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate();

            if ($config->load_base)
            {
                if (!file_exists($template.'/disable-koowa-bootstrap.txt')) {
                    $html .= '<ktml:style src="media://koowa/framework/css/bootstrap.css" />';
                }
            }
            else
            {
                if (file_exists($template.'/enable-koowa-bootstrap.txt')) {
                    $html .= '<ktml:style src="media://koowa/framework/css/bootstrap.css" />';
                }
            }

            self::$_loaded['bootstrap-css'] = true;
        }

        if (!isset(self::$_loaded[$config->package.'-'.$config->file]))
        {
            $template  = 'com_%s/css/%s.css';
            $try_files = array(
                sprintf($template, $config->package, $config->file)
            );

            foreach ($try_files as $file)
            {
                if (file_exists(JPATH_ROOT.'/media/'.$file))
                {
                    $html .= sprintf('<ktml:style src="media://%s" />', $file);

                    self::$_loaded[$config->package.'-'.$config->file] = true;
                    break;
                }
            }
        }

        if ($config->wrapper) {
            $this->wrapper($config);
        }

        return $html;
    }

    /**
     * Wrap the output of the template with a filter
     *
     * @param array|KObjectConfig $config
     */
    public function wrapper($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'wrapper' => null
        ));

        if ($config->wrapper)
        {
            $this->getTemplate()->addFilter('wrapper');
            $this->getTemplate()->getFilter('wrapper')->setWrapper($config->wrapper);
        }
    }
}
