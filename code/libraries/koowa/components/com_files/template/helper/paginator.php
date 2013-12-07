<?php
/**
 * Koowa Framework - http://developer.joomlatools.com/koowa
 *
 * @copyright	Copyright (C) 2011 - 2013 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/koowa-files for the canonical source repository
 */

/**
 * Paginator Template Helper
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */
class ComFilesTemplateHelperPaginator extends ComKoowaTemplateHelperPaginator
{
    /**
     * Render item pagination
     *
     * @param   array   An optional array with configuration options
     * @return  string  Html
     * @see     http://developer.yahoo.com/ypatterns/navigation/pagination/
     */
    public function pagination($config = array())
    {
        $config = new KObjectConfig($config);
        $config->append(array(
            'limit'   => 0,
        ));

        $current_of_total = $this->translate('JLIB_HTML_PAGE_CURRENT_OF_TOTAL');
        if($current_of_total == 'JLIB_HTML_PAGE_CURRENT_OF_TOTAL'
            // Awful fix for Joomla language debug adding ?? before the string
            || strpos(substr($current_of_total, 2), 'JLIB_HTML_PAGE_CURRENT_OF_TOTAL') !== false
        ) {
            $current_of_total = 'Page %s of %s';
        }

        $html  = '<div class="container" id="files-paginator-container">';

        if(version_compare(JVERSION, '3.0', '>=')) {
            $html .= '<div class="pagination pagination-toolbar" id="files-paginator">';
        } else {
            $html .= '<div class="pagination pagination-legacy" id="files-paginator">';
        }

        $html .= '<div class="limit">'.$this->limit($config->toArray()).'</div>';

        if(version_compare(JVERSION, '3.0', '>='))
        {
            $html .= '<span class="start hidden"><a></a></span>';
            $html .= '<ul class="pagination-list">';
            $html .=  $this->_pages();
            $html .= '</ul>';
            $html .= '<span class="end hidden"><a></a></span>';
        }
        else $html .=  $this->_pages();

        $html .= '<div class="limit pull-right"> ';
        $html .= sprintf($current_of_total, '<strong class="page-current">1</strong>', '<strong class="page-total">1</strong></div>');
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a list of pages links
     *
     * This function is overriddes the default behavior to render the links in the khepri template
     * backend style.
     *
     * @param   array   An array of page data
     * @return  string  Html
     */
    protected function _pages($pages = null)
    {
        if(version_compare(JVERSION, '3.0', '>='))
        {
            $tpl = '<li class="%s"><a href="#">%s</a></li>';

            $html  = sprintf($tpl, 'prev', '&larr;');
            $html .= '<li class="page"></li>';
            $html .= sprintf($tpl, 'next', '&rarr;');
        }
        else
        {
            $tpl = '<div class="button2-%s"><div class="%s"><a href="#">%s</a></div></div>';

            $html = sprintf($tpl, 'right', 'start', $this->translate('Start'));
            $html .= sprintf($tpl, 'right', 'prev', $this->translate('Prev'));
            $html .= '<div class="button2-left"><div class="page"></div></div>';
            $html .= sprintf($tpl, 'left', 'next', $this->translate('Next'));
            $html .= sprintf($tpl, 'left', 'end', $this->translate('End'));
        }

        return $html;
    }
}
