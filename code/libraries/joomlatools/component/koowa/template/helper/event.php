<?php
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright   Copyright (C) 2007 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/joomlatools/joomlatools-framework for the canonical source repository
 */

/**
 * Event Template Helper
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package Koowa\Component\Koowa\Template\Helper
 */
class ComKoowaTemplateHelperEvent extends KTemplateHelperAbstract
{
    /**
     * Triggers an event and returns the results
     *
     * @param array $config
     * @throws InvalidArgumentException
     * @return string
     */
    public function trigger($config = array())
    {
        // Can't put arguments through KObjectConfig as it loses referenced variables
        $attributes = isset($config['attributes']) ? $config['attributes'] : array();
        $config     = new KObjectConfig($config);
        $config->append(array(
            'name'         => null,
            'import_group' => null
        ));

        if (empty($config->name)) {
            throw new InvalidArgumentException('Event name is required');
        }

        if(!(count($attributes) > 1)) {
            throw new InvalidArgumentException('Event requires at least 2 attributes');
        }

        if ($config->import_group) {
            JPluginHelper::importPlugin($config->import_group);
        }

        if (class_exists('JEventDispatcher')) {
            $results = JEventDispatcher::getInstance()->trigger($config->name, $attributes);
        } else {
            $results = JFactory::getApplication()->triggerEvent($config->name, $attributes);
        }

        if($config->name == 'onContentPrepare')
        {
            if(isset($attributes[1]) && isset($attributes[1]->text)) {
                $result = $attributes[1]->text;
            }
        }
        else $result = trim(implode("\n", $results));

        // Leave third party JavaScript as-is
        $result = str_replace('<script', '<script data-inline', $result);

        return $result;
    }
}