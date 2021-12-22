<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Module Template Locator
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Locator
 */
class ComKoowaTemplateLocatorModule extends KTemplateLocatorIdentifier
{
    /**
     * The stream name
     *
     * @var string
     */
    protected static $_name = 'mod';

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
        if(defined('JPATH_THEMES'))
        {
            $query = $this->getObject('database')->getQuery('select')
                ->table('template_styles')
                ->columns('template')
                ->where('client_id = :client_id AND home = :home')
                ->bind(array(
                    'client_id' => JFactory::getApplication()->getClientId(), 'home' => 1
                ));

            $template = $this->getObject('database')->select($query, KDatabase::FETCH_FIELD);

            $config->append([
                'override_paths' => [
                    JPATH_THEMES.'/'.$template.'/html',
                    JPATH_THEMES.'/system/html'         // #117: For backwards compatibility purposes
                ]
            ]);
        }

        parent::_initialize($config);
    }

    /**
     * Find a template path
     *
     * @param array  $info      The path information
     * @return bool|mixed
     */
    public function find(array $info)
    {
        $locator = $this->getObject('manager')->getClassLoader()->getLocator('module');

        //Get the package
        $package = $info['package'];

        /*
         * Theme path
         */
        if(!empty($this->_override_paths))
        {
            //Remove the 'view' element from the path.
            $path = $info['path'];
            if(isset($path[0]) && $path[0] == 'view') {
                array_shift($path);
            }

            $path = count($path) ? implode('/', $path).'/' : '';

            foreach ($this->_override_paths as $override_path) {
                //If no type exists create a glob pattern
                if(!empty($info['type'])) {
                    $paths[] = $override_path.'/mod_'.$package.'/'.$path.$info['file'].'.'.$info['format'].'.'.$info['type'];
                } else {
                    $paths[] = $override_path.'/mod_'.$package.'/'.$path.$info['file'].'.'.$info['format'].'.*';
                }
            }
        }

        //Switch basepath
        if(!$locator->getNamespace(ucfirst($package))) {
            $basepath = $locator->getNamespace('\\');
        } else {
            $basepath = $locator->getNamespace(ucfirst($package));
        }

        $basepath .= '/mod_'.strtolower($package);

        $filepath   = (count($info['path']) ? implode('/', $info['path']).'/' : '').'tmpl/';

        //If no type exists create a glob pattern
        if(!empty($info['type'])) {
            $filepath  .= $info['file'].'.'.$info['format'].'.'.$info['type'];
        } else {
            $filepath  .= $info['file'].'.'.$info['format'].'.*';
        }

        $paths[] = $basepath.'/'.$filepath;

        foreach($paths as $path)
        {
            $results = glob($path);

            //Find the file in the directory
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

        return false;
    }
}