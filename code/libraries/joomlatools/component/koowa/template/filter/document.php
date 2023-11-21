<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Feeds information from JDocument into the template to be used in page rendering
 *
 * @author  Ercan Özkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Koowa\Template\Filter
 */
class ComKoowaTemplateFilterDocument extends KTemplateFilterAbstract
{
    /**
     * List of known blacklisted scripts
     * 
     * Supports both full string matching (e.g. `/media/jui/js/jquery.js`) or regular expressions.
     * 
     * Please note that you CANNOT use / as the delimiter for regular expressions. 
     * You can use any other delimiter such as # or ~
     */
    protected $_strip_assets = []; // ['/media/jui/js/jquery.js', '#.*com_content.*#']

    /**
     * Constructor.
     *
     * @param KObjectConfig $config An optional ObjectConfig object with configuration options
     */
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->_strip_assets = array_unique(KObjectConfig::unbox($config->strip_assets));
    }

    /** 
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param  KObjectConfig $config An optional ObjectConfig object with configuration options
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'  => self::PRIORITY_HIGH,
            'strip_assets' => []
        ));

        parent::_initialize($config);
    }

    public function filter(&$text)
    {
        if($this->getTemplate()->decorator() == 'koowa')
        {
            if (version_compare(JVERSION, '5', '>='))
            {
                $document = Joomla\CMS\Factory::getApplication()->getDocument();
    
                $scripts_renderer = new Joomla\CMS\Document\Renderer\Html\ScriptsRenderer($document);
                
                $assets_manager = $document->getWebAssetManager();
    
                $assets = $assets_manager->getAssets('script', true);
    
                $get_importmap = Closure::bind(function ($assets) {
                    return $this->renderImportMap($assets);
                }, $scripts_renderer, $scripts_renderer);
    
                $import_map = $get_importmap($assets);
            }
            else $import_map = '';

            $head = JFactory::getDocument()->getHeadData();
            $mime = JFactory::getDocument()->getMimeEncoding();

            $head['scripts'] = $this->_filterAssets($head['scripts']);
            $head['styleSheets'] = $this->_filterAssets($head['styleSheets']);

            ob_start();

            echo '<title>'.$head['title'].'</title>';

            // Generate stylesheet links
            foreach ($head['styleSheets'] as $source => $attributes)
            {
                if (isset($attributes['mime'])) {
                    $attributes['type'] = $attributes['mime'];
                    unset($attributes['mime']);
                }

                echo sprintf('<ktml:style src="%s" %s />', $source, $this->buildAttributes($attributes));
            }

            // Generate stylesheet declarations
            foreach ($head['style'] as $type => $content)
            {
                // This is for full XHTML support.
                if ($mime != 'text/html') {
                    $content = "<![CDATA[\n".$content."\n]]>";
                }

                if (is_array($content)) {
                    $c = '';
                    foreach ($content as $value) {
                        $c .= "\n".$value."\n";
                    }
                    $content = $c;
                }

                echo sprintf('<style type="%s">%s</style>', $type, $content);
            }

            $document = JFactory::getDocument();

            if (version_compare(JVERSION, '3.7.0', '>='))
            {
                // Copied from \Joomla\CMS\Document\Renderer\Html\MetasRenderer::render
                if (version_compare(JVERSION, '4.0', '>=')) {
                    $wa  = $document->getWebAssetManager();
                    $wc  = [];
                    foreach ($wa->getAssets('script', true) as $asset) {
                        if ($asset instanceof \Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface) {
                            $asset->onAttachCallback($document);
                        }

                        if ($asset->getOption('webcomponent')) {
                            $wc[] = $asset->getUri();
                        }
                    }

                    if ($wc) {
                        $document->addScriptOptions('webcomponents', array_unique($wc));
                    }
                }

                $options  = $document->getScriptOptions();

                $buffer  = '<script type="application/json" class="joomla-script-options new">';
                $buffer .= $options ? json_encode($options, JSON_PRETTY_PRINT) : '{}';
                $buffer .= '</script>';

                echo $buffer;
            } else {
                // Generate script language declarations.
                if (count(JText::script()))
                {
                    echo '<script type="text/javascript">';
                    echo '(function() {';
                    echo 'var strings = ' . json_encode(JText::script()) . ';';
                    echo 'if (typeof Joomla == \'undefined\') {';
                    echo 'Joomla = {};';
                    echo 'Joomla.JText = strings;';
                    echo '}';
                    echo 'else {';
                    echo 'Joomla.JText.load(strings);';
                    echo '}';
                    echo '})();';
                    echo '</script>';
                }
            }

            if ($import_map) echo $import_map;

            // Generate script file links

            if (isset($head['assetManager']) && isset($head['assetManager']['assets']))
            {
                $manager = $head['assetManager']['assets'];

                if (isset($manager['script'])) {
                    /** @var \Joomla\CMS\WebAsset\WebAssetItemInterface $script */
                    foreach ($manager['script'] as $script) {
                        if ($script->getOption('webcomponent') || $script->getOption('importmap')) {
                            continue; // they are loaded by Joomla in core.js
                        }
                        $uri = $script->getUri(true);
                        $attributes = $script->getAttributes();

                        echo sprintf('<ktml:script src="%s" %s />', $uri, $this->buildAttributes($attributes));
                    }
                }
                
                if (isset($manager['style'])) {
                    foreach ($manager['style'] as $style)
                    {
                        if ($uri = $style->getUri(true))
                        {
                            $attributes = $style->getAttributes();

                            if (isset($attributes['type']) && $attributes['type'] === 'module') {
                                unset($attributes['type']);
                            }

                            echo sprintf('<ktml:style src="%s" %s />', $uri, $this->buildAttributes($attributes));
                        }
                     }
                }
            }

            foreach ($head['scripts'] as $path => $attributes)
            {
                if (isset($attributes['mime'])) {
                    $attributes['type'] = $attributes['mime'];
                    unset($attributes['mime']);
                }

                echo sprintf('<ktml:script src="%s" %s />', $path, $this->buildAttributes($attributes));
            }

            // Generate script declarations
            foreach ($head['script'] as $type => $content)
            {
                if (is_array($content)) $content = current($content);

                // This is for full XHTML support.
                if ($mime != 'text/html') {
                    $content = "<![CDATA[\n".$content."\n]]>";
                }

                echo sprintf('<script type="%s">%s</script>', $type, $content);
            }

            foreach ($head['custom'] as $custom) {
                // Inject custom head scripts right before </head>
                $text = str_replace('</head>', $custom."\n</head>", $text);
            }

            $head = ob_get_clean();

            $text = $head.$text;
        }
    }

    /**
     * Filter blacklisted scripts
     *
     * @param $scripts
     * @return array|mixed
     */
    protected function _filterAssets($assets)
    {
        if ($this->_strip_assets)
        {
            $regexps = array_filter($this->_strip_assets, function($path) {
                return isset($path[0]) && in_array($path[0], ['#', '~', '+', '%', '{', '<', '(', '[']);
            });

            $assets = array_filter($assets, function($value, $path) use($regexps) {
                if (in_array($path, $this->_strip_assets)) {
                    return false;
                }

                if ($regexps) {
                    foreach ($regexps as $regexp) {
                        if (preg_match($regexp, $path)) return false;
                    }
                }

                return $value;
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $assets;
    }
}