<?php
/**
 * Tagging Component for Joomlatools Framework - http://developer.joomlatools.com/framework
 *
 * @copyright   Copyright (C) 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-tags for the canonical source repository
 */

/**
 * Listbox Template Helper
 *
 * @author  Tom Janssens <http://github.com/tomjanssens>
 * @package Koowa\Component\Tags
 */
class ComTagsTemplateHelperListbox extends KTemplateHelperListbox
{
    /**
     * Tags listbox helper
     *
     * @param array $config
     * @return string
     */
    public function tags($config = array())
    {
        $config = new KObjectConfig($config);

        // '0' is false in PHP but true in JavaScript so we need to cast to boolean here
        if (isset($config->autocreate)) {
            $config->autocreate = (boolean) $config->autocreate;
        }

        $config->append(array(
            'autocomplete' => false,
            'autocreate'   => true,
            'component'    => $this->getIdentifier()->package,
            'entity'   => null,
            'filter'   => array(),
            'name'     => 'tags',
            'value'    => 'title',
            'prompt'   => false,
            'deselect' => false,
            'attribs'  => array(
                'multiple' => true
            ),
        ))->append(array( // For autocomplete helper
            'model'  => $this->getObject('com:tags.model.tags', array('table' => $config->component.'_tags')),
            'options' => array(
                'tokenSeparators' => ($config->autocreate) ? array(',', '\n', '\t') : array(),
                'tags' => $config->autocreate,
            ),
        ))->append(array( // For listbox helper
            'select2'      => true,
            'select2_options' => array(
                'options' => $config->options
            )
        ));

        $entity = $config->entity;

        //Set the selected tags
        if ($entity instanceof KModelEntityInterface && $entity->isTaggable() && !$entity->isNew())
        {
            $config->append(array(
                'selected' => $entity->getTags()
            ));
        }

        //Set the autocompplete url
        if ($config->autocomplete)
        {
            $parts = array(
                'component' => $config->component,
                'view'      => 'tags',
                'format'    => 'json'
            );

            if ($config->filter) {
                $parts = array_merge($parts, KObjectConfig::unbox($config->filter));
            }

            $config->url = $this->getTemplate()->route($parts, false, false);
        }

        //Do not allow to override label and sort
        $config->label = 'title';
        $config->sort  = 'title';

        $html = parent::_render($config);

        $html .= "<script>
        kQuery(function($) {
            var element = $('select[name=\"{$config->name}[]\"]');
            
            if (element.length) {
                var form = $(element[0].form);
                
                form.submit(function() {
                    if (!element.val()) {
                        $('<input />')
                            .attr('name', '{$config->name}')
                            .attr('type', 'hidden')
                            .val('')
                            .appendTo(form);
                    }
                });
            }
        });</script>";

        return $html;
    }
}
