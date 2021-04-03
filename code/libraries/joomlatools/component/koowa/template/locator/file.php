<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * File Override Locator
 *
 * @author  Johan Janssens <https://www.github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Locator
 */
class ComKoowaTemplateLocatorFile extends KTemplateLocatorFile
{
    /**
     * The override path
     *
     * @var array
     */
    protected $_override_paths = [];

    /**
     * Constructor.
     *
     * @param KObjectConfig $config  An optional KObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_override_paths = $config->override_paths;
    }

    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config An optional KObjectConfig object with configuration options.
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        if(!defined('JOOMLATOOLS_PLATFORM'))
        {
            $query = $this->getObject('database')->getQuery('select')
                ->table('template_styles')
                ->columns('template')
                ->where('client_id = :client_id AND home = :home')
                ->bind(array('client_id' => 0, 'home' => 1));

            $template = $this->getObject('database')->getAdapter()->select($query, KDatabase::FETCH_FIELD);
        }
        else  $template = JFactory::getApplication()->getTemplate();

        $config->append(array(
            'override_paths' => [
                JPATH_ROOT.'/templates/'.$template.'/html',
                JPATH_ROOT.'/templates/system/html'
            ]
        ));

        parent::_initialize($config);
    }

    /**
     * Find a template override
     *
     * @param array  $info      The path information
     * @return bool|mixed
     */
    public function find(array $info)
    {
        //Qualify partial templates.
        if(dirname($info['url']) === '.')
        {
            if(empty($info['base'])) {
                throw new RuntimeException('Cannot qualify partial template path');
            }

            $relative_path = dirname($info['base']);
        }
        else $relative_path = dirname($info['url']);

        $file   = pathinfo($info['url'], PATHINFO_FILENAME);
        $format = pathinfo($info['url'], PATHINFO_EXTENSION);

        $base_paths = array(JPATH_ROOT);

        if (!empty($this->_override_paths)) {
            foreach ($this->_override_paths as $override_path) {
                array_unshift($base_paths, $override_path);
            }
        }

        foreach ($base_paths as $base_path)
        {
            $path = $base_path.'/'.str_replace(parse_url($relative_path, PHP_URL_SCHEME).'://', '', $relative_path);

            // Remove /view from the end of the override path
            if (in_array($base_path, KObjectConfig::unbox($this->_override_paths)) && substr($path, strrpos($path, '/')) === '/view') {
                $path = substr($path, 0, -5);
            }

            if(!$result = $this->realPath($path.'/'.$file.'.'.$format))
            {
                $pattern = $path.'/'.$file.'.'.$format.'.*';
                $results = glob($pattern);

                //Try to find the file
                if ($results)
                {
                    foreach($results as $file)
                    {
                        if($result = $this->realPath($file)) {
                            return $result;
                        }
                    }
                }
            }
        }

        return $result;
    }
}