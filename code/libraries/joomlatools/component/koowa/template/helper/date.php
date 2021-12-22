<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Date Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperDate extends KTemplateHelperDate
{
    /**
     * Returns formatted date according to current local
     *
     * @param  array  $config An optional array with configuration options.
     * @return string Formatted date.
     */
    public function format($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'date'     => 'now',
            'format'   => $this->getObject('translator')->translate('DATE_FORMAT_LC3')
        ));

        if(class_exists('JHtml')) {
            $result = JHtml::_('date', $config->date, $config->format, $config->timezone ? $config->timezone : true);
        } else {
            $result = parent::format($config);
        }

        return $result;
    }
}
