<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Access Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperAccess extends KTemplateHelperAbstract
{
    public function rules($config = array())
    {
        $config = new KObjectConfigJson($config);

        $config->append(array(
            'component' => sprintf('com_%s', $this->getIdentifier()->getPackage()),
            'section'   => 'component',
            'name'      => 'rules',
            'asset'     => null,
            'asset_id'  => 0
        ))->append(array(
            'id' => $config->name
        ));

        // Add editor styles and scripts in JDocument to page when rendering
        $this->getIdentifier('com:koowa.view.page.html')->getConfig()->append(['template_filters' => ['document']]);

        $xml = <<<EOF
<form>
    <fieldset>
        <field name="asset_id" type="hidden" value="{$config->asset_id}" />
        <field name="{$config->name}" type="rules" label="JFIELD_RULES_LABEL"
            translate_label="false" class="inputbox" filter="rules"
            component="{$config->component}" section="{$config->section}" validate="rules"
            id="{$config->id}"  
        />
    </fieldset>
</form>
EOF;

        $form = JForm::getInstance(sprintf('%s.document.acl', $config->component), $xml);
        $form->setValue('asset_id', null, $config->asset_id);

        $html = '<div class="access-rules">'.$form->getInput('rules').'</div>';

        // Do not allow AJAX saving - it tries to guess the asset name with no way to override
        $html = preg_replace('#onchange="sendPermissions[^"]*"#i', '', $html);
        $html = preg_replace('#data-onchange-task="[^"]+"#i', '', $html);

        // Remove Joomla 3 constants from Joomla 4 output. If you remove them from access.xml the actions do not appear in Joomla 3
        $html = preg_replace('#JACTION_[A-Z]+_COMPONENT_DESC#i', '', $html);

        // Add necessary styles
        $html .= '<ktml:style src="media://koowa/com_koowa/css/access.css" />';

        // Tab list is duplicated on every modal opening in Joomla 4.0
        if (version_compare(JVERSION, '4.0', '>=')) {
            $html .= <<<JS
            <script>
            kQuery.magnificPopup.instance.open = function(data) {

                kQuery.magnificPopup.proto.open.call(this,data);

                const extraTabLists = document.querySelectorAll('joomla-field-permissions joomla-tab div[role="tablist"]:not(:first-child)');
    
                for (let tabList of extraTabLists) {
                    tabList.remove();
                }
                
                const tabList = document.querySelector('joomla-field-permissions joomla-tab div[role="tablist"]');
            
                if (tabList) tabList.firstChild.click(); // Pre-select the first usergroup on the layout
            };

            
            </script>
    JS;
        }

        return $html;
    }
}