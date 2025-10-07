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
class ComKoowaTemplateHelperUi extends KTemplateHelperUi
{
    /**
     * Loads the common UI libraries
     *
     * @param array $config
     * @return string
     */
    public function load($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => JFactory::getConfig()->get('debug'),
            'wrapper_class' => array(
                JFactory::getLanguage()->isRtl() ? 'k-ui-rtl' : 'k-ui-ltr',
                ($this->getTemplate()->getConfig()->_restricted ? 'k-ui-restricted' : '')
            )
        ));

        $html = '';

        if($this->getTemplate()->decorator() === 'koowa')
        {
            $layout = $this->getTemplate()->getParameters()->layout;

            if (JFactory::getApplication()->isClient('site') && $layout === 'form') {
                $config->domain = 'admin';
            }
        }

        $identifier = $this->getTemplate()->getIdentifier();
        if($menu = JFactory::getApplication()->getMenu())
        {
            $menu = $menu->getActive();

            if ($identifier->type === 'com' && $menu)
            {
                if ($suffix = htmlspecialchars($menu->getParams()->get('pageclass_sfx', '')))
                {
                    $config->append(array(
                        'wrapper_class' => array($suffix)
                    ));
                }
            }
        }

        $html .= parent::load($config);

        return $html;
    }

    /**
     * Loads admin.css in frontend forms and force-loads Bootstrap if requested
     *
     * @param array $config
     * @return string
     */
    public function styles($config = array())
    {
        $identifier = $this->getTemplate()->getIdentifier();

        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => JFactory::getConfig()->get('debug'),
            'package' => $identifier->package,
            'domain'  => $identifier->domain
        ))->append(array(
            'folder' => 'com_'.$config->package,
            'file'   => ($identifier->type === 'mod' ? 'module' : $config->domain) ?: 'admin',
            'media_path' => JPATH_ROOT . '/media'
        ));

        $html = '';

        $path = sprintf('%s/%s/css/%s.css', $config->media_path, $config->folder, $config->file);

        if (!file_exists($path))
        {
            if ($config->file === 'module') {
                $config->css_file = false;
            } else {
                $config->folder = 'koowa';
            }
        }

        $version = explode('.', JVERSION)[0];

        // Handle Joomla 5+ in the same manner
        if ($version >= 5) {
            $version = 5;
        }

        $ui         = sprintf('k-ui-j%s', $version);
        $classes    = [$ui, sprintf('%s-%s', $ui, JFactory::getApplication()->getName())];

        if ($this->getTemplate()->decorator() == 'joomla')
        {
            $app      = JFactory::getApplication();
            $template = $app->getTemplate();

            // Load Bootstrap file if it's explicitly asked for
            if ($app->isClient('site') && file_exists(JPATH_THEMES.'/'.$template.'/enable-koowa-bootstrap.txt')) {
                $html .= $this->getTemplate()->helper('behavior.bootstrap', ['javascript' => false, 'css' => true]);
            }

            // Load overrides for the current admin template
            if ($app->isClient('administrator') && $config->file === 'admin')
            {
                if (file_exists( JPATH_ROOT . '/media/koowa/com_koowa/css/'.$template.'.css')) {
                    $html .= '<ktml:style src="assets://koowa/css/'.$template.'.css" />';
                }

                if ($version >= 4)
                {
                    if (!KTemplateHelperBehavior::isLoaded($ui))
                    {
                        $html .= '<script data-inline type="text/javascript">
                            
                            // Hide sidebar

                            document.addEventListener("DOMContentLoaded", function()
                            {
                              var wrapper = document.getElementById(\'wrapper\');
                              var menuToggleIcon = document.getElementById(\'menu-collapse-icon\'); 
                              wrapper.classList.add(\'closed\');
                              menuToggleIcon.classList.remove(\'fa-toggle-on\');
                              menuToggleIcon.classList.add(\'fa-toggle-off\');
                              
        
                              let menus = wrapper.querySelectorAll(\'li.mm-active\');
                                                    
                              for (let menu of menus)
                              {
                                menu.classList.remove(\'active\');
                                menu.classList.remove(\'open\');
                                
                                let arrow = menu.querySelector(\'a\');
                                
                                arrow.classList.add(\'mm-collapsed\');
                                arrow.setAttribute(\'aria-expanded\', false);
                                
                                menu.classList.remove(\'mm-active\');
                                menu.classList.remove(\'open\');
                                
                                menu.querySelector(\'ul\').classList.remove(\'mm-show\');
                              }
                              
                              wrapper.querySelector(\'ul.main-nav.metismenu\').classList.add(\'child-open\');
                            });
                        </script>';
                    }
                }
            }
        }
        else $classes[] = sprintf('%s-form', $ui);

        if (!KTemplateHelperBehavior::isLoaded($ui))
        {
            $classes = array_map('json_encode', $classes);
            $html    .= '<script data-inline type="text/javascript">document.documentElement.classList.add(' .
                        implode(", ", $classes) . ');</script>';

            KTemplateHelperBehavior::setLoaded($ui);
        }

        $html .= parent::styles($config);

        return $html;
    }
}
