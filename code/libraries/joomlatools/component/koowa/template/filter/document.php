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
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority' => self::PRIORITY_HIGH
        ));

        parent::_initialize($config);
    }

    public function filter(&$text)
    {
        if($this->getTemplate()->decorator() == 'koowa')
        {
            $head = JFactory::getDocument()->getHeadData();
            $mime = JFactory::getDocument()->getMimeEncoding();

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

                echo sprintf('<style type="%s">%s</style>', $type, $content);
            }

            if (version_compare(JVERSION, '3.7.0', '>=')) {
                $document = JFactory::getDocument();
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

            // Generate script file links
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

            if (isset($head['assetManager']) && isset($head['assetManager']['assets'])) {
                $manager = $head['assetManager']['assets'];

                /** @var \Joomla\CMS\WebAsset\WebAssetItemInterface $script */
                foreach ($manager['script'] as $script) {
                    $uri = $script->getUri(true);
                    $attributes = $script->getAttributes();

                    echo sprintf('<ktml:script src="%s" %s />', $uri, $this->buildAttributes($attributes));
                }

                foreach ($manager['style'] as $style) {
                    $uri = $style->getUri(true);
                    $attributes = $script->getAttributes();

                    echo sprintf('<ktml:style src="%s" %s />', $uri, $this->buildAttributes($attributes));
                }
            }

            $head = ob_get_clean();

            $text = $head.$text;
        }
    }
}