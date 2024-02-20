<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Koowa editor button plugin
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Plugin\Koowa
 */
abstract class PlgKoowaEditorButton extends JPlugin implements PlgKoowaEditorButtonInterface
{
    protected $_editor = null;

    public function __construct(&$subject, $config)
    {
        $this->setEditor(JFactory::getConfig()->get('editor'));

        parent::__construct($subject, $config);
    }

    public function setEditor($name)
    {
        $this->_editor = $name;
        
        return $this;
    }

    public function getEditor()
    {
        return $this->_editor;
    }

    public function getLink($query = '')
    {
        $editor = $this->getEditor();

        if ($editor == 'ckeditor')
        {
            $request = KObjectManager::getInstance()->getObject('request');

            $url = clone $request->getBaseUrl();
    
            $path = $url->getPath(true);
    
            if (empty($path)) $path[] = '';
            
            $path[] = 'index.php';
            
            $url->setPath($path);
            $url->setQuery($query);

            $url = $url->toString();
        }
        else $url = sprintf('index.php?%s', $query);
        
        $link = str_replace('&', '&amp;', $url);

        return $link;
    }
}