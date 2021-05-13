<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Grid Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperGrid extends KTemplateHelperGrid
{
    /**
     * Render an order field
     *
     * @param 	array 	$config An optional array with configuration options
     * @return	string	Html
     */
    public function order($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'entity'    => null,
            'total'     => null,
            'field'     => 'ordering',
            'data'      => array('order' => 0)
        ));

        $translator = $this->getObject('translator');

        $config->data->order = -1;

        $updata = $config->data->toArray();
        $updata = htmlentities(json_encode($updata));

        $config->data->order = +1;

        $downdata = $config->data->toArray();
        $downdata = htmlentities(json_encode($downdata));

        $html = '';

        $html .= '<span class="k-table-data--sort">';

        if ($config->sort === $config->field)
        {
            $tmpl = '
                <a class="k-grid-sort jgrid" href="#" title="%s" data-action="edit" data-data="%s">
                    <span class="state %s">
                        <span class="text">%s</span>
                    </span>
                </a>
                ';
        }
        else
        {
            $tmpl = '
                <span class="k-grid-sort">
                    <span class="state %3$s">
                        <span class="text">%4$s</span>
                    </span>
                </span>';
        }

        if ($config->entity->{$config->field} > 1)
        {
            $icon  = '<span class="k-icon-chevron-top"></span>';
            $html .= sprintf($tmpl, $translator->translate('Move up'), $updata, 'uparrow', $icon);
        } else {
            $html .= '<span class="k-grid-sort k-icon-placeholder"></span>';
        }

        $html .= '<span class="k-grid-sort k-grid-sort--text">' . $config->entity->{$config->field} . '</span>';

        if ($config->entity->{$config->field} != $config->total)
        {
            $icon  = '<span class="k-icon-chevron-bottom"></span>';
            $html .= sprintf($tmpl, $translator->translate('Move down'), $downdata, 'downarrow', $icon);
        }
        else {
            $html .= '<span class="k-grid-sort k-icon-placeholder"></span>';
        }

        if ($config->sort !== $config->field)
        {
            $html .= $this->getTemplate()->helper('behavior.tooltip');

            $html = '<div data-k-tooltip="'.htmlentities(json_encode(array('placement' => 'bottom'))).'"
                          title="'.$translator->translate('Please order by this column first by clicking the column title').'">'
                    .$html.
                    '</div>';
        }

        $html .= '</span>';


        return $html;
    }

    /**
     * Render an access field
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     */
    public function access($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'entity'  		=> null,
            'field'		=> 'access'
        ));

        $db = $this->getObject('joomla')->db;
        $query = $db->getQuery(true);

        $query->select('a.title AS text');
        $query->from('#__viewlevels AS a');
        $query->where('id = '.(int) $config->entity->{$config->field});
        $query->group('a.id, a.title, a.ordering');
        $query->order('a.ordering ASC');
        $query->order($query->qn('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);
        $html = $db->loadResult();

        return $html;
    }
}
