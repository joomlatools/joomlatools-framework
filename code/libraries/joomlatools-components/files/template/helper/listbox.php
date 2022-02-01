<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

class ComFilesTemplateHelperListbox extends ComKoowaTemplateHelperListbox
{
    public function maximum_image_size($config = array())
    {
        $config = new KObjectConfigJson($config);
        $config->append(array(
            'name' => 'maximum_image_size',
            'select2' => true,
            'deselect' => true,
            'selected' => $config->maximum_image_size,
            'options' => [
                ['value' => '1024', 'label' => '1024x1024'],
                ['value' => '2048', 'label' => '2048x2048'],
                ['value' => '3072', 'label' => '3072x3072'],
                ['value' => '4096', 'label' => '4096x4096'],
            ]
        ));

        return parent::optionlist($config);
    }
}