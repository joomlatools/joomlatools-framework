<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Attachments Template Helper
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperAttachments extends KTemplateHelperAbstract
{
    public function manage($config = array())
    {
        $config = new KObjectConfigJson($config);

        if ($entity = $config->entity)
        {
            $table = $entity->getTable();
            $config->append(array('table' => $table->getBase(), 'row' => $entity->id));
        }

        $config->append(array(
            'id'       => 'attachments-manage',
            'value'    => array(),
            'callback' => 'attachmentsCallback',
            'multiple' => true,
            'text'     => $this->getObject('translator')->translate('Manage'),
            'attribs'  => array(
                'data-k-modal' => htmlentities(json_encode(array('mainClass' => 'koowa_dialog_modal koowa_dialog_modal--halfheight'))),
            )
        ))->append(array(
            'link' => $this->getTemplate()->route('view=attachments&layout=manage&tmpl=koowa&table=' .
                                                  urlencode($config->table) . '&row=' . urlencode($config->row) .
                                                  '&callback=' . urlencode($config->callback))
        ));

        $html = '<span class="input-group-btn">';
        $html .= sprintf('<a id="%s" class="btn mfp-iframe" %s href="%s">%s</a>', $config->id, $config->attribs, $config->link, $config->text);
        $html .= '</span>';

        $html .= $this->getTemplate()->createHelper('behavior')->modal();

        return $html;
    }
}