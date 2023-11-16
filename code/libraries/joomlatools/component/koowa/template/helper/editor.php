<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Editor Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperEditor extends KTemplateHelperAbstract
{
    /**
     * Generates an HTML editor
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function display($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'editor'    => JFactory::getConfig()->get('editor'),
            'name'      => 'description',
            'value'     => '',
            'width'     => '100%',
            'height'    => '500',
            'cols'      => '75',
            'rows'      => '20',
            'buttons'   => true,
            'options'   => array()
        ));

        // Add editor styles and scripts in JDocument to page when rendering
        $this->getIdentifier('com:koowa.view.page.html')->getConfig()->append(['template_filters' => ['document']]);

        $editor  = JEditor::getInstance($config->editor);
        $options = KObjectConfig::unbox($config->options);

        $result = $editor->display($config->name, $config->value, $config->width, $config->height, $config->cols, $config->rows, KObjectConfig::unbox($config->buttons), $config->name, null, null, $options);

        // Some editors like CKEditor return inline JS. 
        $result = str_replace('<script', '<script data-inline', $result);

        if (version_compare(JVERSION, '4', '>='))
        {
            $fields = array(
                'joomla-field-media',
                'joomla-field-permissions',
                'joomla-field-simple-color',
                'joomla-media-select',
                'switcher'
            );

            if (JFactory::getLanguage()->isRtl()) {
                $fields[] = 'calendar-rtl';
            } else {
                $fields[] = 'calendar';
            }

            $prepend_path = $this->getObject('request')->getSiteUrl()->getPath(true) ?: [''];

            foreach ($fields as $field)
            {
                $url = $this->getObject('lib:http.url');
                $url->setPath(array_merge($prepend_path, ['media', 'system', 'css', 'fields', sprintf('%s.css', $field)]));

                $result .= sprintf('<ktml:style src="%s" />', $url);
            }
        }
        
        if (version_compare(JVERSION, '5', '>='))
        {
            $document = Joomla\CMS\Factory::getApplication()->getDocument();

            $scripts_renderer = new Joomla\CMS\Document\Renderer\Html\ScriptsRenderer($document);

            // Method 1 - Begin

            $result .= $scripts_renderer->render(null, [], null);

            // Method 1 - End

            // Method 2 - Begin
            /*
            $assets_manager = $document->getWebAssetManager();

            $assets = $assets_manager->getAssets('script', true);

            $get_importmap = Closure::bind(function ($assets) {
                return $this->renderImportMap($assets);
            }, $scripts_renderer, $scripts_renderer);

            $import_map = $get_importmap($assets);

            if ($import_map) {
                $result .= $import_map;
            }
            */

            // Method 2 - End
        }

        return $result;
    }
}
