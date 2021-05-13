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
class ComKoowaTemplateHelperBehavior extends KTemplateHelperBehavior
{
    /**
     * Loads koowa.js
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function koowa($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::koowa($config);
    }

    /**
     * Loads Vue.js
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function vue($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::vue($config);
    }

    /**
     * Loads Modernizr
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function modernizr($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::modernizr($config);
    }

    /**
     * Loads KUI initialize
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function kodekitui($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::kodekitui($config);
    }

    public function modal($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::modal($config);
    }

    /**
     * Loads jQuery under a global variable called kQuery.
     *
     * Loads it from Joomla in 3.0+ and our own sources in 2.5. If debug config property is set, an uncompressed
     * version will be included.
     *
     * You can do window.jQuery = window.$ = window.kQuery; to use the default names
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function jquery($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        $html = '';
        if ($this->getTemplate()->decorator() === 'joomla')
        {
            if (!static::isLoaded('jquery'))
            {
                $this->getObject('joomla')->htmlHelper->_('jquery.framework');

                // Can't use JHtml here as it makes a file_exists call on koowa.kquery.js?version
                $path = $this->getObject('joomla')->uri->root(true).'/media/koowa/framework/js/koowa.kquery.js?'.substr(md5(Koowa::VERSION), 0, 8);
                $this->getObject('joomla')->document->addScript($path);

                static::setLoaded('jquery');
            }
        }
        else $html .= parent::jquery($config);

        return $html;
    }

    /**
     * Add Bootstrap JS and CSS a modal box
     *
     * @param array|KObjectConfig $config
     * @return string   The html output
     */
    public function bootstrap($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        $html = '';

        if ($this->getTemplate()->decorator() === 'joomla')
        {
            $config->append([
                'css' => file_exists($this->getObject('joomla')->getPath('themes').'/'.$this->getObject('joomla')->app->getTemplate().'/enable-koowa-bootstrap.txt')
            ]);

            if ($config->javascript && !static::isLoaded('bootstrap-javascript'))
            {
                $html .= $this->jquery($config);
                $this->getObject('joomla')->htmlHelper->_('bootstrap.framework');

                static::setLoaded('bootstrap-javascript');

                $config->javascript = false;
            }
        }

        $html .= parent::bootstrap($config);
        return $html;
    }

    /**
     * Loads the Forms.Validator class and connects it to Koowa.Controller.Form
     *
     * @param array|KObjectConfig $config
     * @return string   The html output
     */
    public function validator($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::validator($config);
    }

    /**
     * Loads the select2 behavior and attaches it to a specified element
     *
     * @see    http://ivaynberg.github.io/select2/select-2.1.html
     *
     * @param  array|KObjectConfig $config
     * @return string   The html output
     */
    public function select2($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::select2($config);
    }

    /**
     * Loads the Koowa customized jQtree behavior and renders a sidebar-nav list useful in split views
     *
     * @see    http://mbraak.github.io/jqTree/
     *
     * @note   If no 'element' option is passed, then only assets will be loaded.
     *
     * @param  array|KObjectConfig $config
     * @throws InvalidArgumentException
     * @return string    The html output
     */
    public function tree($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::tree($config);
    }

    /**
     * Loads the calendar behavior and attaches it to a specified element
     *
     * @param array|KObjectConfig $config
     * @return string   The html output
     */
    public function calendar($config = array())
    {
        $config = new KObjectConfigJson($config);

        if ($config->filter) {
            $config->offset = strtoupper($config->filter); // @TODO Backwards compatibility
        }

        $config->append(array(
            'debug'          => $this->getObject('joomla')->isDebug(),
            'server_offset'  => $this->getObject('joomla')->config->get('offset'),
            'first_week_day' => $this->getObject('joomla')->language->getFirstDay(),
            'options'        => array(
                'language' => $this->getObject('joomla')->language->getTag(),
            )
        ));

        return parent::calendar($config);
    }

    /**
     * Loads Alpine.js
     *
     * If debug config property is set, an uncompressed version will be included.
     *
     * @param array|KObjectConfig $config
     * @return string
     */
    public function alpine($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'debug' => $this->getObject('joomla')->isDebug()
        ));

        return parent::alpine($config);
    }

}
