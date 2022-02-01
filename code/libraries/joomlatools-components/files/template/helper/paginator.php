<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

/**
 * Paginator Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperPaginator extends KTemplateHelperPaginator
{
    /**
     * Render item pagination
     *
     * @param   array   $config An optional array with configuration options
     * @return  string  Html
     * @see     http://developer.yahoo.com/ypatterns/navigation/pagination/
     */
    public function pagination($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'limit'   => 0,
        ));

        $html = '<div class="k-pagination" id="files-paginator">';

        $html .= '<div class="k-pagination__limit">'.$this->limit($config->toArray()).'</div>';

        $html .= '<span class="start hidden"><a></a></span>';
        $html .= '<ul class="k-pagination__pages pagination">';
        $html .=  $this->_pages(array());
        $html .= '</ul>';
        $html .= '<span class="end hidden"><a></a></span>';

        return $html;
    }

    /**
     * Render a list of pages links
     *
     * This function is overrides the default behavior to render the links in the khepri template
     * backend style.
     *
     * @param   array   $pages An array of page data
     * @return  string  Html
     */
    protected function _pages($pages)
    {
        $tpl = '<li class="%s"><a href="#">%s</a></li>';

        $html  = sprintf($tpl, 'prev', '&laquo;');
        $html .= '<li class="k-js-page"></li>';
        $html .= sprintf($tpl, 'next', '&raquo;');

        return $html;
    }
}
