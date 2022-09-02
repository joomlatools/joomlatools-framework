<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Modal Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperModal extends KTemplateHelperAbstract implements KTemplateHelperParameterizable
{
	public function select($config = array())
	{
        $config = new KObjectConfigJson($config);

        $config->append(array(
            'name' => '',
            'attribs' => array(),
            'button_attribs' => array(),
            'visible' => true,
            'link' => '',
            'link_text' => $this->getObject('translator')->translate('Select'),
            'callback' => '',
        ))->append(array(
            'id' => $config->name,
            'value' => $config->name
        ));

        if ($config->callback) {
            $config->link .= '&callback='.urlencode($config->callback);
        }

        $config->link = preg_replace_callback('#folder=(.*?)&#i', array($this, '_encodingFixer'), $config->link);

        $attribs = $this->buildAttributes($config->attribs);
        $button_attribs = $this->buildAttributes($config->button_attribs);

        $input = '<input name="%1$s" id="%2$s" value="%3$s" %4$s size="40" %5$s />';

        $html = sprintf($input, $config->name, $config->id, $this->getTemplate()->escape($config->value), $config->visible ? 'type="text" readonly' : 'type="hidden"', $attribs);

        $html .= '<span class="input-group-btn">';
        $html .= sprintf('<a data-k-modal class="btn mfp-iframe" %s href="%s">%s</a>', $button_attribs, $config->link, $config->link_text);
        $html .= '</span>';

        $html .= $this->getTemplate()->createHelper('behavior')->modal();

		return $html;
	}

    protected function _encodingFixer($matches)
    {
        return 'folder='.str_replace('+', '%20', $matches[1]).'&';
    }
}
